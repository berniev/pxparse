# pxparse
A set of php classes to read Paradox 4.5 (DOS) VAL, SET and F files and the header section of DB file.

Main classes: PXparseDb, PXparseSet, PXparseVal and PXparseForm.

Usage:

$parser = PX\PXparse{xxx};                   // where {xxx} is 'Val', 'Set', 'Db' or 'Form'
$results = $parser->ParsePx($fileName.{xx}); // where {xx} corresponds to class name
$parser->Draw();                             // (PXparseForm->Draw currently shows no useful data)
Next step is to generate SQL for data table creation and an SQL table containing the remaining data.

It is envisaged this project will be updated from time-to-time.

The following were of assistance:

'PARADOX 4.x FILE FORMATS' by Kevin Mitchell (1996)
The sources of pxlib
github/jsquyres/pxlib-and-pxview