<?php
/* 
 * The MIT License
 *
 * Copyright 2016 vConnect.dk
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * @category Magento
 * @package Vconnect_AllInOne
 * @author vConnect
 * @email kontakt@vconnect.dk
 * @class Prices
 */

namespace Vconnect\Allinone\Block\Adminhtml\System\Config\Form\Field\FieldArray;

class Prices2 extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    protected function _prepareToRender()
    {
        // create columns for the table rate for the carriers(other than pickup) system config 
        $this->addColumn('orderminprice', array(
            'label' => __('Min Price'),
            'size' => 6,
            'style' => 'width: 70px;',
        ));
        $this->addColumn('ordermaxprice', array(
            'label' => __('Max Price'),
            'size' => 6,
            'style' => 'width: 70px;',
        ));
        $this->addColumn('orderminweight', array(
            'label' => __('Min Weight'),
            'size' => 6,
            'style' => 'width: 70px;',
        ));
        $this->addColumn('ordermaxweight', array(
            'label' => __('Max Weight'),
            'size' => 6,
            'style' => 'width: 70px;',
        ));
        $this->addColumn('price', array(
            'label' => __('Shipping Fee'),
            'size' => 6,
            'style' => 'width: 60px;',
        ));
        $this->addColumn('countries', array(
            'label' => __('Country code'),
            'size' => 6,
            'style' => 'width: 70px;',
        ));
        $this->addColumn('transit_time', array(
            'label' => __('Transit time'),
            'size' => 6,
            'style' => 'width: 70px;',
        ));
        $this->addColumn('title', array(
            'label' => __('Title'),
            'size' => 6,
            'style' => 'width: 100px;',
        ));

        $this->_addAfter = false;
    }
}
