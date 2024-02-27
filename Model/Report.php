<?php
/**
 * This file is part of Informes plugin for FacturaScripts
 * Copyright (C) 2022-2024 Carlos Garcia Gomez <carlos@facturascripts.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace FacturaScripts\Plugins\Informes\Model;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Tools;
use FacturaScripts\Core\Where;

/**
 * Description of Report
 *
 * @author Carlos Garcia Gomez <carlos@facturascripts.com>
 */
class Report extends Base\ModelClass
{
    use Base\ModelTrait;

    public const DEFAULT_TYPE = 'area';

    /** @var int */
    public $compared;

    /** @var int */
    public $id;

    /** @var string */
    public $name;

    /** @var string */
    public $table;

    /** @var string */
    public $type;

    /** @var string */
    public $xcolumn;

    /** @var string */
    public $xoperation;

    /** @var string */
    public $ycolumn;

    public function clear(): void
    {
        parent::clear();
        $this->type = self::DEFAULT_TYPE;
    }

    public function getChart()
    {
        $className = 'FacturaScripts\\Dinamic\\Lib\\ReportChart\\' . ucfirst($this->type) . 'Chart';
        if (empty($this->type) || false === class_exists($className)) {
            return '';
        }

        return new $className($this);
    }

    /** @return ReportFilter[] */
    public function getFilters(): array
    {
        $filter = new ReportFilter();
        $where = [new DataBaseWhere('id_report', $this->id)];
        $orderBy = ['table_column' => 'ASC'];
        return $filter->all($where, $orderBy, 0, 0);
    }

    public function getSqlFilters(): string
    {
        $filters = $this->getFilters();
        if (count($filters) === 0) {
            return '';
        }

        $where = [];
        foreach ($filters as $filter) {
            if ($filter->operator === 'IS NULL') {
                $where[] = Where::isNull($filter->table_column);
                continue;
            }

            if ($filter->operator === 'IS NOT NULL') {
                $where[] = Where::isNotNull($filter->table_column);
                continue;
            }

            $where[] = Where::column($filter->table_column, $filter->value, $filter->operator);
        }

        return 'WHERE ' . Where::multiSql($where) . ' ';
    }

    public static function primaryColumn(): string
    {
        return 'id';
    }

    public function primaryDescriptionColumn(): string
    {
        return 'name';
    }

    public static function tableName(): string
    {
        return 'reports';
    }

    public function test(): bool
    {
        $this->name = Tools::noHtml($this->name);
        $this->table = Tools::noHtml($this->table);
        $this->type = Tools::noHtml($this->type);
        $this->xcolumn = Tools::noHtml($this->xcolumn);
        $this->xoperation = Tools::noHtml($this->xoperation);
        $this->ycolumn = Tools::noHtml($this->ycolumn);

        return parent::test();
    }
}
