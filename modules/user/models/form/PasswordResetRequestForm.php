<?php
namespace app\modules\user\models\form;

use Yii;
use yii\base\Model;
use app\modules\user\models\User;
use app\modules\user\Module;

/**
 * Password reset request form
 */
class PasswordResetRequestForm extends Model
{
	public $email;

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			['email', 'filter', 'filter' => 'trim'],
			['email', 'required'],
			['email', 'email'],
			['email', 'exist',
				'targetClass' => '\app\modules\user\models\User',
				'filter' => ['status' => User::STATUS_ACTIVE],
				'message' => 'There is no user with such email.'
			],
		];
	}

	public function attributeLabels()
	{
		return [
			'email' => Module::t('module', 'USER_EMAIL'),
		];
	}

	/**
	 * Sends an email with a link, for resetting the password.
	 *
	 * @return boolean whether the email was send
	 */
	public function sendEmail()
	{
		/* @var $user User */
		$user = User::findOne([
			'status' => User::STATUS_ACTIVE,
			'email' => $this->email,
		]);

		if (!$user)
			return false;

		if (!User::isPasswordResetTokenValid($user->password_reset_token))
			$user->generatePasswordResetToken();

		if (!$user->save())
			return false;

		return Yii::$app->mailer
			->compose(['text' => '@app/modules/user/mail/passwordReset'],
				['user' => $user])
			->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
			->setTo($this->email)
			->setSubject('Password reset for ' . Yii::$app->name)
			->send();
	}
}
