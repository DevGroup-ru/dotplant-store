<?php


namespace DotPlant\Store\components;

use DevGroup\Frontend\Universal\ActionData;
use DotPlant\Monster\DataEntity\DataEntityProvider;
use DotPlant\Store\events\AfterUserRegisteredEvent;
use DotPlant\Store\exceptions\OrderException;
use DotPlant\Store\models\order\Cart;
use DotPlant\Store\models\order\Order;
use DotPlant\Store\models\order\OrderDeliveryInformation;
use DotPlant\Store\Module;
use DevGroup\Users\helpers\ModelMapHelper;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;

class OrderSingleStepProvider extends DataEntityProvider
{
    /**
     * @var string the region key
     */
    public $regionKey = 'orderSingleStepRegion';

    /**
     * @var string the material key
     */
    public $materialKey = 'orderSingleStepMaterial';

    /**
     * @var string the current action route
     */
    public $actionRoute = ['/store/order/create'];

    /**
     * @var array the route to payment action
     */
    public $paymentRoute = ['/store/order/payment'];

    /**
     * @var array the route to cart index action
     */
    public $cartRoute = ['/store/cart'];

    /**
     * @var string the action view file
     */
    public $viewFile = 'single-step-order';

    public function pack()
    {
        return [
            'class' => static::class,
            'entities' => $this->entities,
        ];
    }

    /**
     * @param ActionData $actionData
     *
     * @return mixed
     * @throws BadRequestHttpException
     * @throws OrderException
     */
    public function getEntities(&$actionData)
    {
        $hash = Yii::$app->request->get('hash');
        $order = !empty($hash) ? Store::getOrder($hash) : new Order();
        if ($order === null) {
            throw new BadRequestHttpException();
        }
        $order->autoSaveProperties = true;
        // check cart
        if ($order->isNewRecord) {
            $cart = Store::getCart(false);
            if ($cart === null || $cart->items_count == 0) {
                Yii::$app->session->setFlash('error', Yii::t('dotplant.store', 'Cart is empty'));
                $actionData->controller->redirect($this->cartRoute);
                Yii::$app->end();
            }
            $order->context_id = $cart->context_id;
            if (!$cart->canEdit()) {
                $items = $cart->items;
                $actionData->controller->redirect(
                    ArrayHelper::merge(
                        $this->actionRoute,
                        ['hash' => $cart->order !== null ? $cart->order->hash : null]
                    )
                );
                Yii::$app->end();
            }
            $orderDeliveryInformation = new OrderDeliveryInformation();
            $orderDeliveryInformation->loadDefaultValues();
        }
        // create delivery information if it doesn't exist
        if ($order->isNewRecord || $order->deliveryInformation === null) {
            $orderDeliveryInformation = new OrderDeliveryInformation;
            $orderDeliveryInformation->loadDefaultValues();
        } else {
            $orderDeliveryInformation = $order->deliveryInformation;
        }
        $order->scenario = 'single-step-order';
        $orderDeliveryInformation->context_id = Yii::$app->multilingual->context_id;
        $orderDeliveryInformationIsValid = $orderDeliveryInformation->load(
            Yii::$app->request->post()
        ) && $orderDeliveryInformation->validate();

        $orderIsValid = $order->load(Yii::$app->request->post()) && $order->validate();
        $userId = null;
        if ($orderDeliveryInformationIsValid && $orderIsValid) {
            if ($order->isNewRecord) {
                if (Yii::$app->user->isGuest && Module::module()->registerGuestInCart == 1) {
                    $userClass = ModelMapHelper::User()['class'];
                    $user = new $userClass;
                    $user->username = uniqid("", true);
                    $user->username_is_temporary = true;
                    $user->password_is_temporary = true;
                    $user->email = $orderDeliveryInformation->email;
                    $user->password = Yii::$app->security->generateRandomString(10);
                    if ($user->save(
                        true,
                        [
                            'username',
                            'email',
                            'username_is_temporary',
                            'password_hash',
                            'password_is_temporary',
                            'created_at',
                        ]
                    )
                    ) {
                        Module::module()->trigger(
                            Module::EVENT_AFTER_USER_REGISTERED,
                            new AfterUserRegisteredEvent(
                                [
                                    'languageId' => Yii::$app->multilingual->language_id,
                                    'password' => $user->password,
                                    'userId' => $user->id,
                                ]
                            )
                        );
                        $userId = $user->id;
                    }
                }

                $cart->addDelivery(
                    ArrayHelper::getValue(Yii::$app->request->post(), $order->formName() . '.delivery_id')
                );

                /** @var Cart $cart */
                $order = Store::createOrder($cart);
                if ($order === null) {
                    Yii::$app->session->setFlash(
                        'error',
                        Store::getLastError() !== null ?
                            Store::getLastError() :
                            Yii::t('dotplant.store', 'Something went wrong')
                    );
                    $actionData->controller->redirect(['/store/order/create']);
                    Yii::$app->end();
                }
                $order->scenario = 'single-step-order';
            }
            $order->load(Yii::$app->request->post()); // @todo: refactor it
            $order->created_by = $userId;
            $orderDeliveryInformation->order_id = $order->id;
            $orderDeliveryInformation->user_id = $userId;
            if ($order->save(false) && $orderDeliveryInformation->save()) {
                $actionData->controller->redirect(
                    ArrayHelper::merge($this->paymentRoute, ['hash' => $order->hash, 'paymentId' => $order->payment_id])
                );
                Yii::$app->end();
            }
            Yii::$app->session->setFlash('error', Yii::t('dotplant.store', 'Can not save delivery information'));
            $actionData->controller->redirect(ArrayHelper::merge($this->actionRoute, ['hash' => $order->hash]));
            Yii::$app->end();
        }
        return [
            $this->regionKey => [
                $this->materialKey => [
                    'order' => $order,
                    'orderDeliveryInformation' => $orderDeliveryInformation,
                    'actionRoute' => Url::toRoute(
                        $order->isNewRecord
                            ? $this->actionRoute
                            : array_merge($this->actionRoute, ['hash' => $order->hash])
                    ),
                ],
            ],
        ];
    }
}
