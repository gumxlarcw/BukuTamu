-- Migration: tamdes_rate_limit table for kiosk endpoint throttling
-- Applied: 2026-05-17 (audit finding M4 mitigation)
-- Used by: Api_base::require_rate_limit() — called from /api/kiosk/face-data
-- and /api/kiosk/guest-list (both 30 req/min per IP).
--
-- Not a security perimeter; only slows mass scraping of guest names + face
-- descriptors. For true enumeration prevention, restrict source IPs at the
-- Apache vhost level (Require ip ...).

CREATE TABLE IF NOT EXISTS tamdes_rate_limit (
  id BIGINT NOT NULL AUTO_INCREMENT,
  ip_address VARCHAR(45) NOT NULL,
  endpoint   VARCHAR(64) NOT NULL,
  created_at DATETIME DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY idx_ip_endpoint_time (ip_address, endpoint, created_at),
  KEY idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
