#!/bin/bash
set -e

echo "=========================================="
echo "  MkulimaHub Local Setup"
echo "=========================================="

if ! command -v docker &> /dev/null; then
    echo "Docker is required. Please install Docker first."
    exit 1
fi

echo "→ Starting Docker Compose stack..."
docker compose up --build -d

echo ""
echo "Waiting for services to be ready..."
sleep 15

echo ""
echo "=========================================="
echo "  MkulimaHub is starting!"
echo "=========================================="
echo ""
echo "  Frontend:  http://localhost:5173"
echo "  Backend:   http://localhost:8000"
echo "  API:       http://localhost:8000/api"
echo ""
echo "  Default logins (password: password):"
echo "    Admin:   admin@mkulimahub.co.tz"
echo "    Farmer:  john.farmer@example.com"
echo "    Buyer:   buyer@example.com"
echo ""
echo "  View logs:  docker compose logs -f"
echo "  Stop:       docker compose down"
echo "=========================================="
