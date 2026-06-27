#!/bin/bash

EXECUTABLE_NAME=pint
ROOT="$(pwd)"
ESC_SEQ="\x1b["
COL_RESET=$ESC_SEQ"39;49;00m"
COL_RED=$ESC_SEQ"0;31m"
COL_YELLOW=$ESC_SEQ"0;33m"

echo
printf "$COL_YELLOW%s$COL_RESET\n" "Running pre-commit hook: \"pint\""

locations=(
    "$ROOT/vendor/bin/$EXECUTABLE_NAME"
)

for location in "${locations[@]}"; do
    if [[ -x $location ]]; then
        EXECUTABLE=$location
        break
    fi
done

if [[ ! -x ${EXECUTABLE:-} ]]; then
    printf "$COL_RED%s$COL_RESET\n" "executable $EXECUTABLE_NAME not found, run composer install"
    exit 1
fi

echo "using \"$EXECUTABLE_NAME\" located at $EXECUTABLE"
"$EXECUTABLE" --version

FILES=$(git diff-index --cached --name-only --diff-filter=ACMR HEAD | grep '\.php$' | tr '\n' ' ' | sed 's/ *$//g')

if [ -z "$FILES" ]; then
    echo "No php files staged to fix."
    exit 0
fi

echo "Fixing staged files: $FILES"
"$EXECUTABLE" $FILES
git add $FILES

echo "Okay"
exit 0
