#!/bin/bash

ESC_SEQ="\x1b["
COL_RESET=$ESC_SEQ"39;49;00m"
COL_RED=$ESC_SEQ"0;31m"
COL_YELLOW=$ESC_SEQ"0;33m"

echo
printf "$COL_YELLOW%s$COL_RESET\n" "Running pre-push hook: \"rector\""

if composer rector:dry; then
    echo "Okay"
    exit 0
fi

printf "$COL_RED%s$COL_RESET\n" "Rector found pending refactorings. Run: composer rector && composer pint"
exit 1
