# Findinvoice

Tool to easier the collect of invoices from the bank details (bank reconciliation).

## Getting Started

`git clone git@github.com:remmel/findinvoice.git`
`cd findinvoice`  
`composer install`  
`bin/console server:run`

## Structure

Currently the receipts are split by folder. Each folder is named by the iso month.
Each receipt is named with the concatenation of 
- iso date
- seller (first 2 words of the bank description)
- amount
- comment / others information

```
GDrive
├── db
│   ├── unsorted/
│   ├── transactions.csv (id,date,amount,description) (?currency,?account)
│   └── invoices.csv (date,amount,filename) (?source)
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

- [Weeboob](http://weboob.org/applications/boobank) : OPENSOURCE  
  config: `weboob-config-qt` / accounts: `boobank list -f csv` get all transactions: `boobank history 0123456789@cragr -f json`
- [Bankin](https://docs.bridgeapi.io/docs) : $ - Free sandbox for testing purpose - Bankin is used https://docs.bridgeapi.io/docs
  You will need to create an account https://bridgeapi.io/dashboard/signin if you install that project on your server.  
- [Budget-Insight](https://www.budget-insight.com/budgea-api) : $ - Also collecting documents for many providers
- [Linxo](https://www.linxo.com/api-linxo-connect/) - $$
- [OpenBankProject](api.openbankproject.com) : OPENSOURCE - only german banks
- [SoBank](http://www.sobank.fr/) : $ - EBICS
- https://www.linkedin.com/pulse/complete-whos-who-banking-apis-prateek-sanjay/

### Alternatives

- [IPaidThat](http://ipaidthat.io)
- [Tiime](https://www.tiime.fr/)
- [ReceiptBank](https://www.receipt-bank.com)
- [Azopio](https://app.azopio.com/)

## Features
- Read the invoiced stored (FileSystem; GoogleDrive)
- Import transactions  
  - Automatically (Weboob)  
  - CSV  

## Next features
- Handle cloud drive (✓Google Drive; DropBox)
- Online service
- Use free banking api
- Fetch invoices from mailbox
- Fetch invoices from multiples account (Google Adwords, OVH, Facebook Ad, Amazon...) to fetch invoices
- Store in csv db
- Read the transactions (Bankin API)  
- Add metadata to file

### Metadata to pdf
exiftool -Subject=TOTAL:12.00,CURRENCY:EUR,VAT:2.00,DATE:2018-10-03,COMPANY=ACME, INVOICE_F-201809-207.pdf
pdftk INVOICE_F-201809-207.p
df dump_data

## Problems
- fetch invoices
- OCR invoices
- store metadata in invoices
- fetch transactions (EBICS vs scrapping)