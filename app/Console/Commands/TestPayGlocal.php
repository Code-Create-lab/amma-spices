<?php

namespace App\Console\Commands;

use App\Services\PayGlocalService;
use Illuminate\Console\Command;

class TestPayGlocal extends Command
{
    protected $signature = 'test:payglocal {--debug : Show debug information}';
    protected $description = 'Test PayGlocal integration';

    public function handle()
    {
        $this->info('🧪 Testing PayGlocal Integration');
        $this->info('=================================');

        $payGlocalService = new PayGlocalService();
        
        // Show debug information if requested
        if ($this->option('debug')) {
            $this->showDebugInfo($payGlocalService);
        }
        
        // try {
            $this->info('📝 Preparing test payment data...');
            
            $testData = [
                'order_id' => 'TEST_' . time(),
                'amount' => 100.00,
                'currency' => 'INR',
                'billing_data' => [
                    'firstName' => 'Test',
                    'lastName' => 'User',
                    'emailId' => 'test@example.com',
                    'phoneNumber' => '9876543210',
                    'addressStreet1' => 'Test Street 1',
                    'addressCity' => 'Bangalore',
                    'addressState' => 'Karnataka',
                    'addressPostalCode' => '560001',
                    'addressCountry' => 'IN',
                ]
            ];
            
            $this->info('💳 Test payment data:');
            $this->info('   Order ID: ' . $testData['order_id']);
            $this->info('   Amount: ₹' . $testData['amount']);
            $this->info('   Currency: ' . $testData['currency']);
            
            $this->info('🚀 Initiating payment request...');
            
            $response = $payGlocalService->initiatePayment(12);
            // dd($response);

            $this->info('✅ Payment initiation successful!');
            $this->info('📄 Response:');
            $this->info(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            
            // Show important response fields
            if (isset($response['gid'])) {
                $this->info('🆔 Transaction GID: ' . $response['gid']);
            }
            
            if (isset($response['status'])) {
                $this->info('📊 Status: ' . $response['status']);
            }
            
            if (isset($response['data']['redirectUrl'])) {
                $this->info('🔗 Redirect URL: ' . $response['data']['redirectUrl']);
            }
            
        // } catch (\Exception $e) {
        //     $this->error('❌ Payment initiation failed!');
        //     $this->error('Error: ' . $e->getMessage());
            
        //     // Show more details if debug is enabled
        //     if ($this->option('debug')) {
        //         $this->error('File: ' . $e->getFile() . ':' . $e->getLine());
        //         $this->error('Trace:');
        //         $this->error($e->getTraceAsString());
        //     }
            
        //     $this->info('💡 Troubleshooting tips:');
        //     $this->info('   1. Run: php artisan debug:payglocal');
        //     $this->info('   2. Check your .env configuration');
        //     $this->info('   3. Verify key files are in storage/app/keys/');
        //     $this->info('   4. Check Laravel logs: tail -f storage/logs/laravel.log');
            
        //     return 1;
        // }
        
        return 0;
    }
    
    private function showDebugInfo(PayGlocalService $service)
    {
        $this->info('🔍 Debug Information:');
        $this->info('---------------------');
        
        $debug = $service->debugConfiguration();
        
        foreach ($debug as $key => $value) {
            $status = '';
            if (str_contains($key, '_exists') || str_contains($key, '_readable')) {
                $status = $value ? ' ✅' : ' ❌';
            }
            
            $this->info("   {$key}: " . (is_bool($value) ? ($value ? 'true' : 'false') : $value) . $status);
        }
        
        $this->info('');
    }
}