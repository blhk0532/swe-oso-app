#!/bin/bash
# Helper script to run merinfo_se.py with virtual environment

# Get the directory where this script is located
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Activate virtual environment
source "$SCRIPT_DIR/venv/bin/activate"

# Run the Python script with all arguments passed through
python3 "$SCRIPT_DIR/merinfo_se.py" "$@"
