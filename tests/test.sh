#!/bin/bash

if [ -z $1 ]; then
    echo "Nebyla zadana adresa serveru, ze ktereho se bude provadet test! Adresu je nutne zadat jako prvni argument pri spusteni skriptu."
    exit 1
fi

if [ -z $2 ]; then
    echo "Nebyla zadana adresa API serveru! Adresu je nutne zadat jako druhy argument pri spusteni skriptu."
    exit 1
fi

for N in 10 20 50 100; 
do
  LOG="log/result_"$N".log"
  ab -c $N -n 1000 http://$1/front/rest/get-benchmark/?server=$2 > $LOG
  sleep 5s
done

