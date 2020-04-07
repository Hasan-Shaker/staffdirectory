<?php
declare(strict_types = 1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Causal\Staffdirectory\Tca;

class FeUser
{

    /**
     * Returns the label to be used for a MemberStatus.
     *
     * @param array $params
     * @param null $pObj
     * @return void
     */
    public function getLabel(array &$params, $pObj = null): void
    {
        if (!$params['row']) {
            return;
        }

        $feUser = $params['row'];
        $displayName = !empty($feUser['last_name']) ? $feUser['last_name'] . ', ' : '';
        $displayName .= $feUser['first_name'];
        if (!empty($feUser['title'])) {
            $displayName .= ' (' . $feUser['title'] . ')';
        }

        $params['title'] = $displayName;
    }

}