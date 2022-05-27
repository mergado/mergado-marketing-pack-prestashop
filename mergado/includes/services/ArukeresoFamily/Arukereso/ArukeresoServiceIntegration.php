<?php

namespace Mergado\includes\services\ArukeresoFamily\Arukereso;

use Mergado\includes\services\ArukeresoFamily\Arukereso\ArukeresoService;
use Mergado\includes\services\ArukeresoFamily\AbstractArukeresoFamilyServiceIntegration;

class ArukeresoServiceIntegration extends AbstractArukeresoFamilyServiceIntegration {

	public function __construct() {
        parent::__construct(new ArukeresoService());
	}
}
