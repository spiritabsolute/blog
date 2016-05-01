<?php
namespace app\modules\user\models\form;

use app\modules\user\models\User;
use yii\base\Model;
use yii\db\ActiveQuery;
use Yii;
use app\modules\user\Module;

class ProfileUpdateForm extends Model
{
	public $email;

	/**
	 * @var User
	 */
	private $_user;

	public function __construct(User $user, $config = [])
	{
		$this->_user = $user;
		parent::__construct($config);
	}

	public function init()
	{
		$this->email = $this->_user->email;
		parent::init();
	}

	public function rules()
	{
		return [
			['email', 'required'],
			['email', 'email'],
			[
				'email',
				'unique',
				'targetClass' => User::className(),
				'message' => Module::t('module', 'ERROR_EMAIL_EXISTS'),
				'filter' => function (ActiveQuery $query) {
					$query->andWhere(['<>', 'id', $this->_user->id]);
				},
			],
			['email', 'string', 'max' => 255],
		];
	}

	public function update()
	{
		if ($this->validate()) {
			$user = $this->_user;
			$user->email = $this->email;
			return $user->save();
		} else {
			return false;
		}
	}
}