<?php

namespace app\models;

use app\modules\currency\components\parser\CurrencyParser;
use Faker\Provider\zh_TW\DateTime;
use Yii;

/**
 * This is the model class for table "currency".
 *
 * @property integer $id
 * @property string $valute_id
 * @property string $num_code
 * @property string $char_code
 * @property integer $nominal
 * @property string $name
 * @property string $value
 * @property integer $date
 */
class Currency extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'currency';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['valute_id'], 'required'],
            [['nominal', 'date'], 'integer'],
            [['value'], 'number'],
            [['valute_id', 'num_code', 'char_code'], 'string', 'max' => 16],
            [['name'], 'string', 'max' => 64],
            [['char_code', 'date'], 'unique', 'targetAttribute' => ['char_code', 'date'], 'message' => 'The combination of Char Code and Date has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'valute_id' => 'Valute ID',
            'num_code' => 'Num Code',
            'char_code' => 'Char Code',
            'nominal' => 'Nominal',
            'name' => 'Name',
            'value' => 'Value',
            'date' => 'Date',
        ];
    }

    /**
     * создаст, или обновит запись
     *
     * @param $date d/m/Y
     * @return bool
     */
    public function createOrUpdate($date) {
        //конвертируем дату
        $date = str_replace('/', '-', $date);
        $this->date = strtotime($date);

        //поищем существует ли такая запись
        $model = self::find()
            ->where([
                'char_code' => $this->char_code,
                'date'      => $this->date,
            ])->one();

        //если существует, то перепишем ее
        if($model) {
            $model->attributes = $this->attributes;
            return $model->save();
        }

        //записи нет. создаем ее
        return $this->save();
    }

    /**
     * найдет в базе, или загрузит с сервера курс     *
     *
     * @param $code
     * @param null $date
     * @return Currency
     */
    public static function getByCode($code, $date = null) {
        if($date === null) {
            $date = date('d/m/Y', time());
        }

        $date = str_replace('/', '-', $date);
        $date = strtotime($date);

        $model = self::findOne([
            'char_code' => $code,
            'date' => $date,
        ]);

        //если такой модели нет. загрузим данные от сервера
        if(!$model) {
            $parser = new CurrencyParser();
            $parser->date = date('d/m/Y', $date);
            $parser->initUrl();
            $parser->parse();
            $parser->saveData();

            return self::findOne([
                'char_code' => $code,
                'date' => $date,
            ]);
        }

        return $model;
    }
}
