#!/bin/bash
# Helper functions.

RED='\033[0;31m';
GREEN='\033[0;32m';
YELLOW='\033[0;33m';
BLUE='\033[0;34m';
LIGHT_BLUE='\033[1;34m';
PURPLE='\033[1;35m';
CYAN='\033[1;36m';

NC='\033[0m'; # No Color

function print {

    case $1 in
        red)
            COLOR=${RED}
            ;;
        green)
            COLOR=${GREEN}
            ;;
        yellow)
            COLOR=${YELLOW}
            ;;
        lightBlue)
            COLOR=${LIGHT_BLUE}
            ;;
        blue)
            COLOR=${BLUE}
            ;;
        purple)
            COLOR=${PURPLE}
            ;;
        cyan)
            COLOR=${CYAN}
            ;;
        *)
            COLOR=${NC}
            ;;
    esac

    printf "$COLOR$2${NC}\n";
}
