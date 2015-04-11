<?php

# Parser functions for BioDB
$wgExtensionCredits['parserhook'][] = array(
        'path' => __FILE__,     // Magic so that svn revision number can be shown
        'name' => "BioDB",
        'description' => "Retrieve information from an existing Biological Database",
        'version' => 0.1, 
        'author' => "@toniher",
        'url' => "https://www.mediawiki.org/wiki/User:Toniher",
);

# Define a setup function
$wgHooks['ParserFirstCallInit'][] = 'efBioDBParserFunction_Setup';
# Add a hook to initialise the magic word
$wgHooks['LanguageGetMagic'][]       = 'efBioDBParserFunction_Magic';


# A var to ease the referencing of files
$dir = dirname(__FILE__) . '/';
$wgAutoloadClasses['BioDB'] = $dir . 'BioDB_body.php';

# Store values
# All these parameters should be in LocalSettings.php

$BioDBValues = array();

// We assume MySQL in the future it migth be other options

$wgBioDB = array(
	"server" => "127.0.0.1",
	"type" => "mysql",
	"name" => "mydb",
	"username" => "username",
	"password" => "password",
	"flags" => "",
	"tableprefix" => "",
);

// Exposed info to wiki
// Queried as {{#BioDB_value:goinfo|param}}
// Use query automatically
// TODO: Expose should allow different DB interfaces (not only mysql), now only one!
// TODO: Pending prop map. Define properties somewhere else
// Example table from: http://greenc.sciencedesigners.com

$wgBioDBExpose = array(
	"gene" => array(
		"query" => "SELECT distinct(c.gene_alias) AS gene_alias
			, n.gene_name AS gene_name
			, c.chromosome AS chromosome
			, c.start AS start
			, c.end AS end
			, c.strand AS strand
			, c.source AS source
			, c.assembly AS assembly
			, c.annotation AS annotation
			, c.taxon_id AS taxonid
			, c.coding_nat AS codingnature
			FROM coordinates c
			LEFT
			JOIN gene_names n
			ON n.gene_alias = c.gene_alias
			WHERE c.gene_alias = '#P1' group by gene_alias;",
		"propmap" => array( // Mapping to SMW properties
			"gene_alias" => "Has Alias",
			"gene_name" => "Has Name",
			"chromosome" => "Is in Chromosome",
			"start" => "Has Location Start",
			"end" => "Has Location End",
			"strand" => "Is in Strand",
			"source" => "Has Source",
			"assembly" => "Is in Assembly",
			"annotation" => "Has Annotation",
			"codingnature" => "Is Coding"
		)
	)
);

function efBioDBParserFunction_Setup( &$parser ) {
	$parser->setFunctionHook( 'BioDB', 'BioDB::executeBioDBret', SFH_OBJECT_ARGS );
	$parser->setFunctionHook( 'BioDB_value', 'BioDB::doExternalValue', SFH_OBJECT_ARGS );
	$parser->setFunctionHook( 'BioDB_table', 'BioDB::doForExternalTable', SFH_OBJECT_ARGS );
	$parser->setFunctionHook( 'BioDB_store_table', 'BioDB::doStoreExternalTable' );
	$parser->setFunctionHook( 'BioDB_clear', 'BioDB::doClearExternalData' );
	return true;
}

function efBioDBParserFunction_Magic( &$magicWords, $langCode ) {
	$magicWords['BioDB'] = array( 0, 'BioDB' );
	$magicWords['BioDB_value'] = array( 0, 'BioDB_value' );
	$magicWords['BioDB_table'] = array( 0, 'BioDB_table' );
	$magicWords['BioDB_store_table'] = array( 0, 'BioDB_store_table' );
	$magicWords['BioDB_clear'] = array( 0, 'BioDB_clear' );
	# unless we return true, other parser functions extensions won't get loaded.
	return true;
}



