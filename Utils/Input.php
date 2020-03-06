<?php namespace Utils;

use LasseRafn\Initials\Initials;

class Input
{
	public $name;
	public $length;
	public $size;
	public $fontSize;
	public $background;
	public $color;
	public $cacheKey;
	public $rounded;
	public $uppercase;
	public $initials;
	public $bold;
	public $format;

	private $hasQueryParameters = false;

	private static $indexes = [
		'name',
		'size',
		'background',
		'color',
		'length',
		'font-size',
		'rounded',
		'uppercase',
		'bold',
		'format'
	];

	public function __construct() {
		$this->detectQueryParameters();
		$this->detectUrlBasedParameters();

		$this->name       = $_GET['name'] ?? 'John Doe';
		$this->size       = (int) ( $_GET['size'] ?? 64 );
		$this->background = $_GET['background'] ?? '#ddd';
		$this->color      = $_GET['color'] ?? '#222';
		$this->length     = (int) ( $_GET['length'] ?? 2 );
		$this->fontSize   = (double) ( $_GET['font-size'] ?? 0.5 );

		$this->bold   = $this->getBold();
		$this->rounded   = $this->getRounded();
		$this->uppercase = $this->getUppercase();
		$this->initials  = $this->getInitials();
		$this->format    = $this->getFormat();
		$this->cacheKey  = $this->generateCacheKey();
		$this->fixInvalidInput();
	}

	private function getRounded() {
		return filter_var( $_GET['rounded'] ?? false, FILTER_VALIDATE_BOOLEAN );
	}

	private function getBold() {
		return filter_var( $_GET['bold'] ?? false, FILTER_VALIDATE_BOOLEAN );
	}

	private function getUppercase() {
		return filter_var( $_GET['uppercase'] ?? true, FILTER_VALIDATE_BOOLEAN );
	}

	private function getInitials() {
		return ( new Initials )->length( $this->length )->keepCase( ! $this->uppercase )->generate( $this->name );
	}

	private function getFormat() {
		if (in_array($_GET['format'] ?? '', ['png', 'svg'], true)) {
			return $_GET['format'];
		}

		return strpos( $_SERVER['HTTP_ACCEPT'] ?? $_REQUEST['Accept'] ?? '', 'image/svg+xml' ) !== false ? 'svg' : 'png';
	}

	private function generateCacheKey() {
		return md5( "{$this->initials}-{$this->length}-{$this->size}-{$this->fontSize}-{$this->background}-{$this->color}-{$this->rounded}-{$this->uppercase}-{$this->bold}" );
	}

	private function fixInvalidInput() {
		if ( $this->length <= 0 ) {
			$this->length = 1;
		}

		if ( $this->fontSize <= 0 ) {
			$this->fontSize = 0.5;
		}

		if ( $this->fontSize > 1 ) {
			$this->fontSize = 1;
		}

		if ( $this->size <= 15 ) {
			$this->size = 16;
		}

		if ( $this->size > 512 ) {
			$this->size = 512;
		}
	}

	private function detectQueryParameters() {
		foreach ( $_GET as $item => $value ) {
			if ( \in_array( $item, self::$indexes, true ) ) {
				$this->hasQueryParameters = true;

				return true;
			}
		}

		return false;
	}

	private function detectUrlBasedParameters() {
		if ( $this->hasQueryParameters ) {
			return false;
		}

		$requestUrl = ltrim( $_SERVER['REQUEST_URI'], '/' );
		$requestUrl = ltrim( $requestUrl, 'api' );
		$requestUrl = ltrim( $requestUrl, '/' );

		foreach ( explode( '/', $requestUrl ) as $index => $value ) {
			if ( ! isset( self::$indexes[ $index ] ) ) {
				continue;
			}

			$_GET[ self::$indexes[ $index ] ] = urldecode( $value );
		}

		return true;
	}
}
