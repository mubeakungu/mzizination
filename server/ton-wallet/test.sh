#!/bin/bash
curl -s -X POST http://localhost:3001/wallet/generate \
  -H "Content-Type: application/json" \
  -d '{"payment_id":999}'
