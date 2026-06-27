#!/bin/bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
HOOKS_DIR="$ROOT/.git/hooks"

install_runner() {
    local hook_name="$1"
    local target="$HOOKS_DIR/$hook_name"

    cat > "$target" <<EOF
#!/bin/bash
set -e

HOOKS_FOLDER=".git_hooks"
HOOK_NAME="$hook_name"

cd "\$(git rev-parse --show-toplevel)"

for script in ./\$HOOKS_FOLDER/\$HOOK_NAME/*; do
    if [ -f "\$script" ]; then
        bash "\$script"
    fi
done
EOF

    chmod +x "$target"
    echo "Installed $target"
}

if [ ! -d "$ROOT/.git" ]; then
    echo "Not a git repository: $ROOT"
    exit 1
fi

install_runner pre-commit
install_runner pre-push

echo "Git hooks installed."
