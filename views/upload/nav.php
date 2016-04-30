<?php

use orchidphp\Orchid;

/**
 * @todo write description
 *
 * @package    
 * @author     Matus Macak <matus.macak@orchidsphere.com>
 * @copyright  2015 OrchidSphere
 * @link       http://orchidsphere.com/
 * @license    License here
 * @version    1.0.0
 */
$active = 0;
?>
<ul class="nav nav-pills nav-stacked"><?php

    $href = Orchid::base()->urlresolver->buildHref('upload/files', [], ENTRY_SCRIPT_URL, $active);
    
    ?><li role="presentation" <?php echo ($active == 1)? 'class="active"' : ''?>>
        <a href="<?php echo $href; ?>"><?php echo Orchid::t('Upload activity') ?></a>
    </li><?php

    $href = Orchid::base()->urlresolver->buildHref('upload/manual', [], ENTRY_SCRIPT_URL, $active);

    ?><li role="presentation" <?php echo ($active == 1)? 'class="active"' : ''?>>
        <a href="<?php echo $href; ?>"><?php echo Orchid::t('Add manual entry') ?></a>
    </li>
</ul>