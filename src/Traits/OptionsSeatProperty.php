<?php

namespace Daaner\NovaPoshta\Traits;

use Exception;

trait OptionsSeatProperty
{
    protected $OptionsSeat;
    protected $Weight;

    /**
     * Параметр груза для каждого места отправления.
     *
     * Поддерживается два формата ввода:
     *   1) Готовый список мест в виде массива массивов с явными ключами
     *      (weight, volumetricWidth, volumetricLength, volumetricHeight,
     *      volumetricVolume) — используется «как есть»:
     *
     *          $doc->setOptionsSeat([
     *              ['weight' => '1', 'volumetricWidth' => '10',
     *               'volumetricLength' => '10', 'volumetricHeight' => '10'],
     *          ]);
     *
     *   2) Индекс/список индексов из `config('novaposhta.options_seat')`
     *      (строка `'1'`, `'1,2'` или массив `[1, 2]`) — для обратной
     *      совместимости с прежним поведением.
     *
     * @param  string|array  $OptionsSeat
     * @return $this
     */
    public function setOptionsSeat($OptionsSeat): self
    {
        if ($this->isExplicitOptionsSeatList($OptionsSeat)) {
            $this->OptionsSeat = array_values($OptionsSeat);

            return $this;
        }

        $data = config('novaposhta.options_seat');
        if (is_array($OptionsSeat) === false) {
            $OptionsSeat = explode(',', /** @scrutinizer ignore-type */ $OptionsSeat);
        }
        foreach ($OptionsSeat as $value) {
            try {
                $this->OptionsSeat[] = $data[$value];
            } catch (Exception $e) {
                $this->OptionsSeat[] = $data[1];
            }
        }

        return $this;
    }

    /**
     * Проверяет, что переданный аргумент — уже подготовленный список
     * мест (массив массивов с обязательным ключом `weight`).
     *
     * @param  mixed  $OptionsSeat
     * @return bool
     */
    protected function isExplicitOptionsSeatList($OptionsSeat): bool
    {
        if (! is_array($OptionsSeat) || $OptionsSeat === []) {
            return false;
        }

        foreach ($OptionsSeat as $item) {
            if (! is_array($item) || ! array_key_exists('weight', $item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return void
     */
    public function getOptionsSeat(): void
    {
        if (! $this->OptionsSeat) {
            $defaultSeat = [
                'volumetricVolume' => '1',
                'volumetricWidth' => '24',
                'volumetricLength' => '17',
                'volumetricHeight' => '10',
                'weight' => '1',
            ];

            $this->OptionsSeat = $defaultSeat;
        }
        $this->methodProperties['OptionsSeat'] = $this->OptionsSeat;
    }

    /**
     * Устанавливаем вес груза. По умолчанию значение из конфига.
     * Не обязательно, если выставляем OptionsSeat, но это не точно.
     *
     * @param  string  $weight  Вес груза
     * @return $this
     */
    public function setWeight(string $weight): self
    {
        $this->Weight = $weight;

        return $this;
    }

    /**
     * Установка веса.
     *
     * @return void
     */
    public function getWeight(): void
    {
        $this->methodProperties['Weight'] = $this->Weight ?: config('novaposhta.weight');
    }
}
