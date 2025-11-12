#!/bin/bash
# Wrapper script to run ratsit_se.py with virtual environment

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
VENV_DIR="$SCRIPT_DIR/venv"

# Activate virtual environment
if [ ! -d "$VENV_DIR" ]; then
    echo "Error: Virtual environment not found at $VENV_DIR"
    echo "Please run: python3 -m venv scripts/venv && source scripts/venv/bin/activate && pip install -r scripts/requirements.txt"
    exit 1
fi

source "$VENV_DIR/bin/activate"

# Run the script with all arguments
exec python "$SCRIPT_DIR/ratsit_se.py" "$@"


