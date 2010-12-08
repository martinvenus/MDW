#!/bin/bash
for N in 10 20 50 100 150 200 300 400 500; 
do
  LOG="result_"$N".log"
  ab -c $N -n 1000 http://localhost/MDW/document_root/front/rest/get-benchmark/?server=localhost/MDW/document_root > $LOG
  sleep 5s
done

