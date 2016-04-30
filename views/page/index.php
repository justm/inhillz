<?php

use orchidphp\Orchid;

/**
 * Súbor predstavuje náhľad pre domovskú stránku
 *
 * @package    inhillz\views
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @link       http://ride.inhillz.com/
 * @version    2.0
 */
?><div class="col-xs-12"><?php

    echo 'Welcome ' . Orchid::base()->authenticate->getFullName();

?></div>