# pxparse
php classes to interpret Paradox 4.5 (DOS) DB, VAL, SET, F and Xxn files

Parses DB, VAL, SET and XGn files to produce:
* pxinfo.sql (field data for each table)
* pxcreate.sql (create statements including primary and secondary keys)
* pxdata.sql (insert statements for extracted data)

Currently does not extract data from encrypted DB files.
