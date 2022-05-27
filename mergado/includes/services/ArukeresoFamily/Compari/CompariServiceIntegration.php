<?php

namespace Mergado\includes\services\ArukeresoFamily\Compari;

use Mergado\includes\services\ArukeresoFamily\Compari\CompariService;
use Mergado\includes\services\ArukeresoFamily\AbstractArukeresoFamilyServiceIntegration;

class CompariServiceIntegration extends AbstractArukeresoFamilyServiceIntegration {

	public function __construct() {
        parent::__construct(new CompariService());
	}
}
