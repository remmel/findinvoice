# Findinvoice

Tool to facilite the collect of invoice (bank reconciliation).

## Getting Started

checkout
composer
run php server : php -S localhost:8000  
configure parameters.dumb.php

## Structure

Currently the receipts are split by folder. Each folder is named by the iso month.
Each receipt is named with the concatenation of 
- iso date
- seller (first 2 words of the bank description)
- amount
- others information


```
/home/remmel/Dropbox/administrative/accountant/accounting/receipts
├── 2018-01
│   ├── 2018-01-03_Adobe_29.99_Adobe_Transaction-No-0891058534-20180330.pdf
│   ├── 2018-01-03_Facebk_33.00_2018-03-31T17-30-Transaction-n-1422984147813481-3453781.pdf
│   ├── 2018-01-03_Ovh Roubaix_11.99_Facture-FR23557144.pdf
│   └── 2018-01-13_Amazon_23.71_Invoice.pdf
└── 2018-02
    ├── 2018-02-05_Ratp_29.80.pdf
    └── 2018-02-06_La Poste_45.60.pdf
```

## Others tools and links

### Banking API

Bankin is used https://docs.bridgeapi.io/docs
Others providers : https://www.linkedin.com/pulse/complete-whos-who-banking-apis-prateek-sanjay/

### Alternatives

- http://ipaidthat.io

## Next features
- handle cloud drive (DropBox, Google Drive / Google Suite)
- online service
- use free banking api
- connect to mailbox to fetch invoices
- connect to multiples account (Google Adwords, OVH, Facebook Ad, Amazon...) to fetch invoices