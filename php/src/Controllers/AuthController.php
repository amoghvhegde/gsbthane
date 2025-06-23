<?php

namespace App\Controllers;

use App\Config\SupabaseConfig;

/**
 * Controller for authentication operations with Supabase
 */
class AuthController
{
    /**
     * Sign up a new user
     */
    public function signUp(string $email, string $password): array
    {
        $url = SupabaseConfig::getUrl() . '/auth/v1/signup';
        $key = SupabaseConfig::getKey();
        
        $data = [
            'email' => $email,
            'password' => $password
        ];
        
        $response = $this->makeRequest($url, 'POST', $data, $key);
        
        if (isset($response['error'])) {
            return [
                'success' => false,
                'message' => $response['error_description'] ?? $response['error'] ?? 'Signup failed',
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Signup successful. Please check your email to confirm your account.',
            'user' => $response['user'] ?? null
        ];
    }
    
    /**
     * Sign in a user
     */
    public function signIn(string $email, string $password): array
    {
        $url = SupabaseConfig::getUrl() . '/auth/v1/token?grant_type=password';
        $key = SupabaseConfig::getKey();
        
        $data = [
            'email' => $email,
            'password' => $password
        ];
        
        $response = $this->makeRequest($url, 'POST', $data, $key);
        
        if (isset($response['error'])) {
            return [
                'success' => false,
                'message' => $response['error_description'] ?? $response['error'] ?? 'Login failed',
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Login successful',
            'access_token' => $response['access_token'] ?? null,
            'refresh_token' => $response['refresh_token'] ?? null,
            'user' => $response['user'] ?? null
        ];
    }
    
    /**
     * Sign out a user
     */
    public function signOut(string $accessToken): array
    {
        $url = SupabaseConfig::getUrl() . '/auth/v1/logout';
        $key = SupabaseConfig::getKey();
        
        $headers = [
            'Authorization: Bearer ' . $accessToken
        ];
        
        $response = $this->makeRequest($url, 'POST', [], $key, $headers);
        
        return [
            'success' => true,
            'message' => 'Logged out successfully'
        ];
    }
    
    /**
     * Reset password
     */
    public function resetPassword(string $email): array
    {
        $url = SupabaseConfig::getUrl() . '/auth/v1/recover';
        $key = SupabaseConfig::getKey();
        
        $data = [
            'email' => $email
        ];
        
        $response = $this->makeRequest($url, 'POST', $data, $key);
        
        if (isset($response['error'])) {
            return [
                'success' => false,
                'message' => $response['error_description'] ?? $response['error'] ?? 'Password reset failed',
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Password reset email sent. Please check your email.'
        ];
    }
    
    /**
     * Get user by access token
     */
    public function getUser(string $accessToken): array
    {
        $url = SupabaseConfig::getUrl() . '/auth/v1/user';
        $key = SupabaseConfig::getKey();
        
        $headers = [
            'Authorization: Bearer ' . $accessToken
        ];
        
        $response = $this->makeRequest($url, 'GET', [], $key, $headers);
        
        if (isset($response['error'])) {
            return [
                'success' => false,
                'message' => $response['error_description'] ?? $response['error'] ?? 'Failed to get user data',
            ];
        }
        
        return [
            'success' => true,
            'user' => $response
        ];
    }
    
    /**
     * Make an HTTP request to Supabase
     */
    private function makeRequest(string $url, string $method, array $data = [], string $apiKey = '', array $extraHeaders = []): array
    {
        $ch = curl_init();
        
        $headers = array_merge([
            'Content-Type: application/json',
            'apikey: ' . $apiKey
        ], $extraHeaders);
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'error' => 'cURL Error',
                'error_description' => $error
            ];
        }
        
        return json_decode($response, true) ?? [];
    }
}