<?php

namespace Composer;

use Composer\Semver\VersionParser;
use OutOfBoundsException;


class InstalledVersions
{
private static $installed = array(
	'root'     =>
		array(
			'pretty_version' => 'dev-master',
			'version'        => 'dev-master',
			'aliases'        =>
				array(),
			'reference'      => '0c47f11d692193172a79f64611175167c78bd190',
			'name'           => '__root__',
		),
	'versions' =>
		array(
			'__root__'                           =>
				array(
					'pretty_version' => 'dev-master',
					'version'        => 'dev-master',
					'aliases'        =>
						array(),
					'reference'      => '0c47f11d692193172a79f64611175167c78bd190',
				),
			'cmb2/cmb2'                          =>
				array(
					'pretty_version' => 'v2.9.0',
					'version'        => '2.9.0.0',
					'aliases'        =>
						array(),
					'reference'      => 'cacbc8cedbfdf8ffe0e840858e6860f9333c33f2',
				),
			'juvo/wp-admin-notices'              =>
				array(
					'pretty_version' => 'v1.0.2',
					'version'        => '1.0.2.0',
					'aliases'        =>
						array(),
					'reference'      => '53129ec20d2bef9aac6291638d924a44fa7781c4',
				),
			'wptrt/admin-notices'                =>
				array(
					'pretty_version' => 'v1.0.3',
					'version'        => '1.0.3.0',
					'aliases'        =>
						array(),
					'reference'      => '3904e0dc087b48289056a77eea3d1dab4ef0dde1',
				),
			'yahnis-elsts/plugin-update-checker' =>
				array(
					'pretty_version' => 'v4.11',
					'version'        => '4.11.0.0',
					'aliases'        =>
						array(),
					'reference'      => '3155f2d3f1ca5e7ed3f25b256f020e370515af43',
				),
		),
);







public static function getInstalledPackages()
{
return array_keys(self::$installed['versions']);
}









public static function isInstalled($packageName)
{
return isset(self::$installed['versions'][$packageName]);
}














public static function satisfies(VersionParser $parser, $packageName, $constraint)
{
$constraint = $parser->parseConstraints($constraint);
$provided = $parser->parseConstraints(self::getVersionRanges($packageName));

return $provided->matches($constraint);
}










public static function getVersionRanges($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
	throw new OutOfBoundsException( 'Package "' . $packageName . '" is not installed' );
}

$ranges = array();
if (isset(self::$installed['versions'][$packageName]['pretty_version'])) {
$ranges[] = self::$installed['versions'][$packageName]['pretty_version'];
}
if (array_key_exists('aliases', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['aliases']);
}
if (array_key_exists('replaced', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['replaced']);
}
if (array_key_exists('provided', self::$installed['versions'][$packageName])) {
$ranges = array_merge($ranges, self::$installed['versions'][$packageName]['provided']);
}

return implode(' || ', $ranges);
}





public static function getVersion($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
	throw new OutOfBoundsException( 'Package "' . $packageName . '" is not installed' );
}

if (!isset(self::$installed['versions'][$packageName]['version'])) {
return null;
}

return self::$installed['versions'][$packageName]['version'];
}





public static function getPrettyVersion($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
	throw new OutOfBoundsException( 'Package "' . $packageName . '" is not installed' );
}

if (!isset(self::$installed['versions'][$packageName]['pretty_version'])) {
return null;
}

return self::$installed['versions'][$packageName]['pretty_version'];
}





public static function getReference($packageName)
{
if (!isset(self::$installed['versions'][$packageName])) {
	throw new OutOfBoundsException( 'Package "' . $packageName . '" is not installed' );
}

if (!isset(self::$installed['versions'][$packageName]['reference'])) {
return null;
}

return self::$installed['versions'][$packageName]['reference'];
}





public static function getRootPackage()
{
return self::$installed['root'];
}







public static function getRawData()
{
return self::$installed;
}



















public static function reload($data)
{
self::$installed = $data;
}
}
