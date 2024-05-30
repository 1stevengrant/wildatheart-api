#!/bin/bash
./vendor/bin/pint $1> /dev/null && cat $1
