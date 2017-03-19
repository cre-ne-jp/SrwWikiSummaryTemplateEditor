.PHONY: doc doc-php doc-js clean

doc: doc-php doc-js

doc-php:
	doxygen

doc-js:
	jsdoc --destination doc/js modules

clean:
	rm -rf doc/*
