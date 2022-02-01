<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('verification_code')->nullable();
            $table->string('note')->nullable();
            $table->string('reason')->nullable();
            $table->enum('status', ['scheduled','pending', 'preparing', 'ready', 'enroute', 'failed', 'cancelled', 'delivered'])->default('pending');
            $table->enum('payment_status', ['pending', 'review', 'failed', 'cancelled', 'successful'])->default('pending');
            $table->double('delivery_fee', 15, 2)->default(0);
            $table->double('total', 15, 2)->default(0);
            $table->date('pickup_date')->nullable();
            $table->time('pickup_time')->nullable();
            $table->foreignId('package_type_id')->nullable()->constrained();
            $table->double('weight', 10, 2)->default(0);
            //end package delivery columns
            $table->string('productPrice')->nullable();
            $table->string('merchantAddress')->nullable();
            $table->string('deliveryHub')->nullable();
            $table->string('customerAddress')->nullable();
            $table->string('customerName')->nullable();


            $table->foreignId('payment_method_id')->constrained();
            $table->foreignId('vendor_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('driver_id')->nullable()->constrained( "users");
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
