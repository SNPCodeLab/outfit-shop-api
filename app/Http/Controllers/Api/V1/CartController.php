<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartController extends BaseApiController
{
    /**
     * Get or create active shopping cart for customer or guest session.
     */
    private function resolveCart(Request $request): Cart
    {
        $customerId = $request->user()?->customer_id
            ?? $request->input('customer_id')
            ?? $request->header('X-Customer-Id');

        $sessionId = $request->input('session_id')
            ?? $request->header('X-Session-Id');

        $cart = null;

        if ($customerId) {
            $cart = Cart::where('customer_id', $customerId)
                ->where('status', 'ACTIVE')
                ->latest()
                ->first();

            if (! $cart) {
                $cart = Cart::create([
                    'customer_id' => $customerId,
                    'session_id' => $sessionId ?? (string) Str::uuid(),
                    'status' => 'ACTIVE',
                    'currency' => 'USD',
                ]);
            }
        } elseif ($sessionId) {
            $cart = Cart::where('session_id', $sessionId)
                ->where('status', 'ACTIVE')
                ->latest()
                ->first();

            if (! $cart) {
                $cart = Cart::create([
                    'session_id' => $sessionId,
                    'status' => 'ACTIVE',
                    'currency' => 'USD',
                ]);
            }
        } else {
            $newSession = (string) Str::uuid();
            $cart = Cart::create([
                'session_id' => $newSession,
                'status' => 'ACTIVE',
                'currency' => 'USD',
            ]);
        }

        return $cart;
    }

    /**
     * Format cart payload with complete line items, variant options, and financial calculations.
     */
    private function formatCart(Cart $cart): array
    {
        $cart->load([
            'items.variant.product.category',
            'items.variant.product.images',
            'items.variant.size',
            'items.variant.color',
        ]);

        $subtotal = 0.0;
        $totalItems = 0;
        $itemsList = [];

        foreach ($cart->items as $item) {
            $unitPrice = (float) ($item->unit_price ?? $item->variant?->price ?? 0.0);
            $lineTotal = $unitPrice * $item->quantity;
            $subtotal += $lineTotal;
            $totalItems += $item->quantity;

            $itemsList[] = [
                'cart_item_id' => $item->cart_item_id,
                'cart_id' => $item->cart_id,
                'variant_id' => $item->variant_id,
                'quantity' => $item->quantity,
                'unit_price' => $unitPrice,
                'line_total' => round($lineTotal, 2),
                'product' => [
                    'product_id' => $item->variant?->product?->product_id,
                    'product_name' => $item->variant?->product?->product_name,
                    'category' => $item->variant?->product?->category?->category_name,
                    'image_url' => $item->variant?->product?->primary_image_url ?? $item->variant?->image_url,
                ],
                'variant' => [
                    'sku' => $item->variant?->sku,
                    'barcode' => $item->variant?->barcode,
                    'size' => $item->variant?->size?->size_name,
                    'color' => $item->variant?->color?->color_name,
                    'color_hex' => $item->variant?->color?->hex_code,
                    'in_stock' => $item->variant?->quantity ?? 0,
                ],
            ];
        }

        $taxRate = 10.00; // 10% standard VAT
        $taxAmount = round($subtotal * ($taxRate / 100), 2);
        $grandTotal = round($subtotal + $taxAmount, 2);

        return [
            'cart_id' => $cart->cart_id,
            'session_id' => $cart->session_id,
            'customer_id' => $cart->customer_id,
            'status' => $cart->status,
            'currency' => $cart->currency ?? 'USD',
            'total_items' => $totalItems,
            'subtotal' => round($subtotal, 2),
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
            'items' => $itemsList,
        ];
    }

    /**
     * Display the contents and calculation of the active cart.
     * Public / Authenticated.
     */
    public function index(Request $request): JsonResponse
    {
        $cart = $this->resolveCart($request);

        return $this->successResponse($this->formatCart($cart), 'Cart retrieved successfully');
    }

    /**
     * Add an item / product variant to the cart.
     */
    public function addItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'variant_id' => 'required|exists:product_variants,variant_id',
            'quantity' => 'nullable|integer|min:1|max:100',
            'customer_id' => 'nullable|integer|exists:customers,customer_id',
            'session_id' => 'nullable|string|max:100',
        ]);

        $cart = $this->resolveCart($request);
        $variant = ProductVariant::findOrFail($validated['variant_id']);
        $quantity = (int) ($validated['quantity'] ?? 1);

        $cartItem = CartItem::where('cart_id', $cart->cart_id)
            ->where('variant_id', $variant->variant_id)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $quantity;
            $cartItem->unit_price = $variant->price;
            $cartItem->save();
        } else {
            $cartItem = CartItem::create([
                'cart_id' => $cart->cart_id,
                'variant_id' => $variant->variant_id,
                'quantity' => $quantity,
                'unit_price' => $variant->price,
            ]);
        }

        return $this->createdResponse(
            $this->formatCart($cart),
            'Item added to cart successfully'
        );
    }

    /**
     * Update quantity of a specific cart item.
     */
    public function updateItem(Request $request, int $itemId): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0|max:100',
        ]);

        $cartItem = CartItem::findOrFail($itemId);

        if ($validated['quantity'] === 0) {
            $cart = $cartItem->cart;
            $cartItem->delete();

            return $this->successResponse($this->formatCart($cart), 'Item removed from cart');
        }

        $cartItem->quantity = $validated['quantity'];
        $cartItem->save();

        return $this->successResponse(
            $this->formatCart($cartItem->cart),
            'Cart item updated successfully'
        );
    }

    /**
     * Remove a single item from the cart.
     */
    public function removeItem(int $itemId): JsonResponse
    {
        $cartItem = CartItem::findOrFail($itemId);
        $cart = $cartItem->cart;
        $cartItem->delete();

        return $this->successResponse($this->formatCart($cart), 'Item removed from cart');
    }

    /**
     * Clear all items in the active cart.
     */
    public function clear(Request $request): JsonResponse
    {
        $cart = $this->resolveCart($request);
        $cart->items()->delete();

        return $this->successResponse($this->formatCart($cart), 'Cart cleared successfully');
    }
}
