<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
}

/**
 * ParserFirstCallInit hook handler.
 * Magic words are registered via i18n/magic.json.
 * Extension metadata and class autoloading are declared in extension.json.
 *
 * In LocalSettings.php, configure the extension before wfLoadExtension():
 *
 *   $wgBioDB = [
 *       'server'      => 'localhost',
 *       'type'        => 'mysql',
 *       'name'        => 'mydb',
 *       'username'    => 'myuser',
 *       'password'    => 'mypassword',
 *       'flags'       => 0,
 *       'tableprefix' => '',
 *   ];
 *   $wgBioDBExpose = [ ... ];
 *
 *   wfLoadExtension( 'BioDB' );
 */
function wfBioDBParserFunction_Setup( Parser $parser ) {
	$parser->setFunctionHook( 'BioDB', [ BioDB::class, 'executeBioDB' ], SFH_OBJECT_ARGS );
	$parser->setFunctionHook( 'BioDB_value', [ BioDB::class, 'doExternalValue' ], SFH_OBJECT_ARGS );
	$parser->setFunctionHook( 'BioDB_count', [ BioDB::class, 'doCountValue' ], SFH_OBJECT_ARGS );
	$parser->setFunctionHook( 'BioDB_exists', [ BioDB::class, 'doExistsValue' ], SFH_OBJECT_ARGS );
	$parser->setFunctionHook( 'BioDB_table', [ BioDB::class, 'doForExternalTable' ], SFH_OBJECT_ARGS );
	$parser->setFunctionHook( 'BioDB_table_template', [ BioDB::class, 'doForExternalTableTemplate' ], SFH_OBJECT_ARGS );
	$parser->setFunctionHook( 'BioDB_clear', [ BioDB::class, 'doClearExternalData' ] );
}
