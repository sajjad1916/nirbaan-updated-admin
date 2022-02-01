<?php

namespace App\Traits;

use App\Models\CityVendor;
use App\Models\Order;
use App\Models\User;
use App\Models\UserToken;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\WebPushConfig;
use Illuminate\Support\Facades\App;

trait FirebaseMessagingTrait
{

    use FirebaseAuthTrait;


    //
    private function sendFirebaseNotification(
        $topic,
        $title,
        $body,
        array $data = null,
        bool $onlyData = true,
        string $channel_id = "basic_channel",
        bool $noSound = false
    ) {

        //getting firebase messaging
        $messaging = $this->getFirebaseMessaging();
        $messagePayload = [
            'topic' => $topic,
            'notification' => $onlyData ? null : [
                'title' => $title,
                'body' => $body,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                "channel_id" => $channel_id,
                "sound" => $noSound ? "" : "alert.aiff",
            ],
            'data' => $data,
        ];

        if (!$onlyData) {
            $messagePayload = [
                'topic' => $topic,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    "channel_id" => $channel_id,
                    "sound" => $noSound ? "" : "alert.aiff",
                ],
            ];
        } else {

            if (empty($data["title"])) {
                $data["title"] = $title;
                $data["body"] = $body;
            }
            $messagePayload = [
                'topic' => $topic,
                'data' => $data,
            ];
        }
        $message = CloudMessage::fromArray($messagePayload);

        //android configuration
        $androidConfig = [
            'ttl' => '3600s',
            'priority' => 'high',
            'data' => $data,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                "channel_id" => $channel_id,
                "sound" => $noSound ? "" : "alert",
            ],
        ];
        
        if ($onlyData) {
            if (empty($data["title"])) {
                $data["title"] = $title;
                $data["body"] = $body;
            }
            $androidConfig = [
                'ttl' => '3600s',
                'priority' => 'high',
                'data' => $data,
            ];
        }
        $config = AndroidConfig::fromArray($androidConfig);

        $message = $message->withAndroidConfig($config);
        $messaging->send($message);
    }

    private function sendFirebaseNotificationToTokens(array $tokens, $title, $body, array $data = null)
    {
        if (!empty($tokens)) {
            //getting firebase messaging
            $messaging = $this->getFirebaseMessaging();
            $message = CloudMessage::new();
            //
            $config = WebPushConfig::fromArray([
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'icon' => setting('websiteLogo', asset('images/logo.png')),
                ],
                'fcm_options' => [
                    'link' => $data[0],
                ],
            ]);
            //
            $message = $message->withWebPushConfig($config);
            $messaging->sendMulticast($message, $tokens);
        }
    }










    //
    public function sendOrderStatusChangeNotification(Order $order)
    {


        //order data
        $orderData = [
            'is_order' => "1",
            'order_id' => (string)$order->id,
        ];

        //
        $managersId = $order->vendor->managers->pluck('id')->all() ?? [];
        $managersTokens = UserToken::whereIn('user_id', $managersId)->pluck('token')->toArray();

        //'pending','preparing','ready','enroute','delivered','failed','cancelled'
        if ($order->status == "pending") {
            $this->sendFirebaseNotification($order->user_id, __("New Order"), __("Your order is pending"), $orderData);
            //web
            $this->sendFirebaseNotificationToTokens($managersTokens, __("New Order"), __("Order #") . $order->code . __(" has just been placed with you"), [route('orders')]);
            //vendor
            $this->sendFirebaseNotification("v_" . $order->vendor_id, __("New Order"), __("Order #") . $order->code . __(" has just been placed with you"), $orderData);
        } else if ($order->status == "preparing") {
            $this->sendFirebaseNotification($order->user_id, __("Order Update"), __("Your order is now being prepared"), $orderData);
        } else if ($order->status == "ready") {
            $this->sendFirebaseNotification($order->user_id, __("Order Update"), __("Your order is now ready for delivery/pickup"), $orderData);
        } else if ($order->status == "enroute") {

            //web
            $this->sendFirebaseNotificationToTokens($managersTokens, __("Order Update"), __("Order #") . $order->code . __(" has been assigned to a delivery boy"), [route('orders')]);


            //user
            $this->sendFirebaseNotification($order->user_id, __("Order Update"), __("Order #") . $order->code . __(" has been assigned to a delivery boy"), $orderData);
            //vendor
            $this->sendFirebaseNotification("v_" . $order->vendor_id, __("Order Update"), __("Order #") . $order->code . __(" has been assigned to a delivery boy"), $orderData);
        } else if ($order->status == "delivered") {
            //user/customer
            $this->sendFirebaseNotification($order->user_id, __("Order Update"), __("Order #") . $order->code . __(" has been delivered"), $orderData);
            //vendor
            $this->sendFirebaseNotification("v_" . $order->vendor_id, __("Order Update"), __("Order #") . $order->code . __(" has been delivered"), $orderData);

            //driver
            if (!empty($order->driver_id)) {
                $this->sendFirebaseNotification(
                    $order->driver_id,
                    __("Order Update"),
                    __("Order #") . $order->code . __(" has been delivered"),
                    $orderData
                );
            }
        } else if (!empty($order->status)) {
            $this->sendFirebaseNotification($order->user_id, __("Order Update"), __("Order #") . $order->code . __(" has been ") . __($order->status) . "", $orderData);
        }


        //send notifications to admin & city-admin
        //admin 
        if (setting("notifyAdmin", 0)) {
            //sending notification to admin accounts
            $adminsIds = User::admin()->pluck('id')->all();
            $adminTokens = UserToken::whereIn('user_id', $adminsIds)->pluck('token')->toArray();
            //
            $this->sendFirebaseNotificationToTokens(
                $adminTokens,
                __("Order Notification"),
                __("Order #") . $order->code . " " . __("with") . " " . $order->vendor->name . " " . __("is now:") . " " . $order->status,
                [route('orders')]
            );
        }
        //city-admin 
        if (setting("notifyCityAdmin", 0) && !empty($order->vendor->creator_id)) {
            //sending notification to city-admin accounts
            $cityAdminTokens = UserToken::where('user_id', $order->vendor->creator_id)->pluck('token')->toArray();
            //
            $this->sendFirebaseNotificationToTokens(
                $cityAdminTokens,
                __("Order Notification"),
                __("Order #") . $order->code . " " . __("with") . " " . $order->vendor->name . " " . __("is now:") . " " . $order->status,
                [route('orders')]
            );
        }
    }


    public function sendOrderNotificationToDriver(Order $order)
    {


        //order data
        $orderData = [
            'is_order' => "1",
            'order_id' => (string)$order->id,
        ];

        //
        $this->sendFirebaseNotification(
            $order->driver_id,
            __("Order Update"),
            __("Order #") . $order->code . __(" has been assigned to you"),
            $orderData
        );

    }

    //notificat chat parties
    public function sendChatNotification(Order $order)
    {
        // //chat sample
        // $this->sendFirebaseNotification($topic, $this->headings, $this->message, [
        //     'is_chat' => "1",
        //     'code' => "hfjh27hj",
        //     'vendor' => json_encode([
        //         "id" => 1,
        //         "name" => "Meme Inc.",
        //         "photo" => "https://img.icons8.com/cute-clipart/344/apple-app-store.png",
        //     ]),
        //     'user' => json_encode([
        //         "id" => 6,
        //         "name" => "Client User",
        //         "photo" => "https://img.icons8.com/cute-clipart/344/apple-app-store.png",
        //     ]),
        // ]);
    }
}
