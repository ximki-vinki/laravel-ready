#!/bin/bash

ROOT_DIR="$(pwd)/"
LIST=$(git diff-index --cached --name-only --diff-filter=ACMR HEAD)
ERRORS_BUFFER=""
ESC_SEQ="\x1b["
COL_RESET=$ESC_SEQ"39;49;00m"
COL_RED=$ESC_SEQ"0;31m"
COL_YELLOW=$ESC_SEQ"0;33m"

echo
printf "$COL_YELLOW%s$COL_RESET\n" "Running pre-commit hook: \"php-linter\""

for file in $LIST; do
    if echo "$file" | grep -qE '\.php$'; then
        ERRORS=$(php -l "$ROOT_DIR$file" 2>&1 | grep "Parse error")
        if [ "$ERRORS" != "" ]; then
            if [ "$ERRORS_BUFFER" != "" ]; then
                ERRORS_BUFFER="$ERRORS_BUFFER\n$ERRORS"
            else
                ERRORS_BUFFER="$ERRORS"
            fi
            echo "Syntax errors found in file: $file"
        fi
    fi
done

if [ "$ERRORS_BUFFER" != "" ]; then
    echo
    echo "These errors were found in try-to-commit files:"
    echo -e "$ERRORS_BUFFER"
    echo
    printf "$COL_RED%s$COL_RESET\n\n" "Can't commit, fix errors first."
    exit 1
fi

echo "Okay"
exit 0
