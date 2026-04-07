#!/bin/bash
# Wrapper para Laravel Boost MCP — funciona tanto no host (via Docker) quanto dentro do container.

cd "$(dirname "$0")/.." || exit 1

if [ -f /.dockerenv ]; then
    exec php vendor/bin/testbench boost:mcp
else
    exec docker compose exec -T php php vendor/bin/testbench boost:mcp
fi
