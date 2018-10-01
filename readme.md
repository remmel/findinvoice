# Findinvoice

Tool to easier the collect of invoices from the bank details (bank reconciliation).

2 projects :
- legacy : vanilly php without database (deleted once migrated)
- symfony : sf project

## Getting Started

`git clone git@github.com:remmel/findinvoice.git`
`cd findinvoice`  
`composer install`  

Legacy project : 
- run php server : `php -S localhost:8000 -t publiclegacy/`
- configure parameters.dumb.php

Symfony project :
- `bin/console server:run`

## Structure

Currently the receipts are split by folder. Each folder is named by the iso month.
Each receipt is named with the concatenation of 
- iso date
- seller (first 2 words of the bank description)
- amount
- comment / others information

```
GDrive
├── 2018-01
│   ├── 2018-01-03_Adobe_29.99_Adobe_Transaction-No-0891058534-20180330.pdf
│   ├── 2018-01-03_Facebk_33.00_2018-03-31T17-30-Transaction-n-1422984147813481-3453781.pdf
│   ├── 2018-01-03_Ovh-Roubaix_11.99_Facture-FR23557144.pdf
│   └── 2018-01-13_Amazon_23.71_Invoice.pdf
└── 2018-02
    ├── 2018-02-05_Ratp_29.80.pdf
    └── 2018-02-06_La Poste_45.60.pdf
```

## Others tools and links

### Banking API

Bankin is used https://docs.bridgeapi.io/docs - You will need to create an account https://bridgeapi.io/dashboard/signin if you install that project on your server.  
Others providers : https://www.linkedin.com/pulse/complete-whos-who-banking-apis-prateek-sanjay/

### Alternatives

- http://ipaidthat.io

## Features
- Read the invoiced stored (FileSystem; GoogleDrive)
- Read the transactions (Bankin API)  

## Next features
- Handle cloud drive (✓Google Drive; DropBox)
- Online service
- Use free banking api
- Fetch invoices from mailbox
- Fetch invoices from multiples account (Google Adwords, OVH, Facebook Ad, Amazon...) to fetch invoices
- Store in csv db
