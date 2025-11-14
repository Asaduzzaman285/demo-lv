<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class ProductCreationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that API requires Shopify authentication headers.
     *
     * @return void
     */
    public function test_requires_shopify_headers()
    {
        // Send request WITHOUT headers
        $response = $this->postJson('/api/products', [
            'title' => 'Test Product',
            'variations' => [
                [
                    'title' => 'Red / Small',
                    'price' => '10.00'
                ]
            ]
        ]);

        // Assert 400 Bad Request
        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Missing Shopify shop domain or access token'
                 ]);
    }

    /**
     * Test that API validates required fields.
     *
     * @return void
     */
    public function test_validates_required_fields()
    {
        // Send request with MISSING required fields
        $response = $this->postJson('/api/products', [], [
            'X-Shopify-Shop-Domain' => 'test.myshopify.com',
            'X-Shopify-Access-Token' => 'test-token'
        ]);

        // Assert 422 Unprocessable Entity
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['title', 'variations']);
    }

    /**
     * Test that variations array must have at least one item.
     *
     * @return void
     */
    public function test_validates_variations_array_minimum()
    {
        $response = $this->postJson('/api/products', [
            'title' => 'Test Product',
            'variations' => [] // Empty array
        ], [
            'X-Shopify-Shop-Domain' => 'test.myshopify.com',
            'X-Shopify-Access-Token' => 'test-token'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['variations']);
    }

    /**
     * Test that variation structure is validated correctly.
     *
     * @return void
     */
    public function test_validates_variation_structure()
    {
        $response = $this->postJson('/api/products', [
            'title' => 'Test Product',
            'variations' => [
                [
                    'title' => 'Red / Small'
                    // Missing required 'price' field
                ]
            ]
        ], [
            'X-Shopify-Shop-Domain' => 'test.myshopify.com',
            'X-Shopify-Access-Token' => 'test-token'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['variations.0.price']);
    }

    /**
     * Test that price must be numeric and non-negative.
     *
     * @return void
     */
    public function test_validates_price_is_numeric_and_positive()
    {
        $response = $this->postJson('/api/products', [
            'title' => 'Test Product',
            'variations' => [
                [
                    'title' => 'Red / Small',
                    'price' => 'invalid-price' // Not numeric
                ]
            ]
        ], [
            'X-Shopify-Shop-Domain' => 'test.myshopify.com',
            'X-Shopify-Access-Token' => 'test-token'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['variations.0.price']);
    }

    /**
     * Test that inventory quantity must be integer when provided.
     *
     * @return void
     */
    public function test_validates_inventory_quantity_is_integer()
    {
        $response = $this->postJson('/api/products', [
            'title' => 'Test Product',
            'variations' => [
                [
                    'title' => 'Red / Small',
                    'price' => '10.00',
                    'inventory_quantity' => 'not-an-integer'
                ]
            ]
        ], [
            'X-Shopify-Shop-Domain' => 'test.myshopify.com',
            'X-Shopify-Access-Token' => 'test-token'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['variations.0.inventory_quantity']);
    }

    /**
     * Test that image src must be valid URL.
     *
     * @return void
     */
    public function test_validates_image_src_is_url()
    {
        $response = $this->postJson('/api/products', [
            'title' => 'Test Product',
            'variations' => [
                [
                    'title' => 'Red / Small',
                    'price' => '10.00',
                    'images' => [
                        [
                            'src' => 'not-a-valid-url' // Invalid URL
                        ]
                    ]
                ]
            ]
        ], [
            'X-Shopify-Shop-Domain' => 'test.myshopify.com',
            'X-Shopify-Access-Token' => 'test-token'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['variations.0.images.0.src']);
    }

}