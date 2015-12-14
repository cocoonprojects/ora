CREATE TABLE IF NOT EXISTS event_stream (
  "eventId"        VARCHAR(200) NOT NULL,
  "version"        int4 NOT NULL,
  "eventName"      TEXT NOT NULL,
  "payload"        TEXT NOT NULL,
  "occurredOn"     TEXT NOT NULL,
  "aggregate_id"   TEXT NOT NULL,
  "aggregate_type" TEXT NOT NULL,
  PRIMARY KEY ("eventId")
)