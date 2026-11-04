#!/bin/sh
# SeaweedFS in one container (master, volume, filer and S3 gateway) for the
# files of the edition resources. Writes the S3 credentials from the
# environment, starts the server, creates the bucket and marks the container
# ready for the healthcheck.
set -eu

: "${S3_ACCESS_KEY:?Set S3_ACCESS_KEY in .env}"
: "${S3_SECRET_KEY:?Set S3_SECRET_KEY in .env}"
: "${S3_BUCKET:?Set S3_BUCKET in .env}"

rm -f /tmp/ready
cat > /tmp/s3.json <<JSON
{
  "identities": [
    {
      "name": "library",
      "credentials": [{ "accessKey": "${S3_ACCESS_KEY}", "secretKey": "${S3_SECRET_KEY}" }],
      "actions": ["Admin", "Read", "List", "Tagging", "Write"]
    }
  ]
}
JSON

weed server -dir=/data -ip=127.0.0.1 -ip.bind=0.0.0.0 -s3 -s3.port=8333 -s3.config=/tmp/s3.json &
server=$!
trap 'kill -TERM "$server"; wait "$server"' TERM INT

# The master takes a few seconds to elect itself and accept shell commands
for _ in $(seq 1 60); do
    if echo "s3.bucket.list" | weed shell -master=127.0.0.1:9333 >/dev/null 2>&1; then
        break
    fi
    sleep 1
done

# Idempotent: the bucket survives restarts in the seaweedfs_data volume
if ! echo "s3.bucket.list" | weed shell -master=127.0.0.1:9333 2>/dev/null | grep -qw "${S3_BUCKET}"; then
    echo "s3.bucket.create -name ${S3_BUCKET}" | weed shell -master=127.0.0.1:9333
fi
echo "Bucket ${S3_BUCKET} ready"
touch /tmp/ready

wait "$server"
