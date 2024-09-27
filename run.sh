#!/bin/bash

# Function to log messages
log() {
    echo "$(date +'%Y-%m-%d %H:%M:%S') - $1"
}

# Function to clean up and exit
cleanup() {
    log "INFO: Cleaning up and exiting..."
    # Kill the PHP development server process and all its child processes
    if [[ -n "$server_pid" ]]; then
        # Kill the PHP server process group
        kill -- -"$server_pid"
    fi
    exit 0
}

# Trap SIGINT signal (Ctrl+C) and call the cleanup function
trap cleanup SIGINT

# Check if PHP is installed
if ! command -v php &>/dev/null; then
    log "ERROR: PHP is not installed. Please install PHP before running this script."
    exit 1
fi

# Check if Ollama is installed
if ! command -v ollama &>/dev/null; then
    log "ERROR: Ollama is not installed. Please install Ollama before running this script."
    exit 1
fi

# Navigate to the directory of the script
cd "$(dirname "$0")" || exit
log "INFO: Script directory: $(pwd)"

# Fetch updates from the Git repository
log "INFO: Fetching updates from the Git repository..."
git fetch

# Check if updates are available
if [[ $(git rev-parse HEAD) != $(git rev-parse @{u}) ]]; then
    # Pull updates from the Git repository if available
    log "INFO: Updates available. Pulling..."
    git pull
else
    # No updates available
    log "INFO: Already up to date."
fi

# Clear the terminal screen
clear

# Start the PHP development server in the background
log "INFO: Starting PHP development server..."
php -S localhost:8000 &
# Store the process ID (PID) of the server
server_pid=$!

# Log the server start
log "INFO: PHP development server started on http://localhost:8000"

# Open the URL in the default browser
log "INFO: Opening URL in default browser..."
case "$(uname -s)" in
Darwin) open http://localhost:8000 ;;
Linux) xdg-open http://localhost:8000 ;;
CYGWIN* | MINGW32* | MSYS* | MINGW*) start http://localhost:8000 ;;
*) log "ERROR: Unsupported operating system." ;;
esac

# Wait for the PHP development server process to finish
wait "$server_pid"
