<?php

namespace Mergado\includes\services\ArukeresoFamily\Pazaruvaj;

use Mergado\includes\services\ArukeresoFamily\Pazaruvaj\PazaruvajService;
use Mergado\includes\services\ArukeresoFamily\AbstractArukeresoFamilyServiceIntegration;

class PazaruvajServiceIntegration extends AbstractArukeresoFamilyServiceIntegration {

	public function __construct() {
        parent::__construct(new PazaruvajService());
	}
}
