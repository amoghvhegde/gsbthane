<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Api\ApiClient;
use App\Config\ApiConfig;
use App\Models\ApiMember;

// Enable error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Create API client instance
$apiClient = new ApiClient(ApiConfig::getBaseUrl());

// Function to display member information
function displayMember($member) {
    echo "<div class='member'>";
    echo "<h3>{$member->getFirstName()} {$member->getLastName()}</h3>";
    echo "<p><strong>Email:</strong> {$member->getEmail()}</p>";
    echo "<p><strong>Phone:</strong> " . ($member->getPhone() ?: 'N/A') . "</p>";
    echo "<p><strong>Membership Level:</strong> {$member->getMembershipLevel()}</p>";
    echo "<p><strong>Join Date:</strong> {$member->getJoinDate()}</p>";
    echo "<p><strong>Status:</strong> " . ($member->isActive() ? 'Active' : 'Inactive') . "</p>";
    echo "</div>";
}

echo "<h1>Member API Example</h1>";

// Authenticate with the API
try {
    if ($apiClient->authenticate(ApiConfig::getDefaultAdminEmail(), ApiConfig::getDefaultAdminPassword())) {
        echo "<div class='success'>Authentication successful!</div>";
        
        // Example 1: Get all members
        echo "<h2>All Members</h2>";
        $members = ApiMember::getAll();
        
        if (empty($members)) {
            echo "<p>No members found.</p>";
        } else {
            echo "<div class='members-grid'>";
            foreach ($members as $member) {
                displayMember($member);
            }
            echo "</div>";
        }
        
        // Example 2: Create a new member
        echo "<h2>Create New Member</h2>";
        
        // Check if form was submitted
        if (isset($_POST['create_member'])) {
            try {
                $newMember = new ApiMember(
                    null,
                    $_POST['first_name'],
                    $_POST['last_name'],
                    $_POST['email'],
                    $_POST['phone'],
                    $_POST['address'],
                    $_POST['membership_level'],
                    $_POST['join_date']
                );
                
                if ($newMember->save()) {
                    echo "<div class='success'>Member created successfully!</div>";
                } else {
                    echo "<div class='error'>Failed to create member.</div>";
                }
            } catch (Exception $e) {
                echo "<div class='error'>Error: {$e->getMessage()}</div>";
            }
        }
        
        // Member creation form
        echo "
        <form method='post' action=''>
            <div class='form-group'>
                <label for='first_name'>First Name:</label>
                <input type='text' id='first_name' name='first_name' required>
            </div>
            
            <div class='form-group'>
                <label for='last_name'>Last Name:</label>
                <input type='text' id='last_name' name='last_name' required>
            </div>
            
            <div class='form-group'>
                <label for='email'>Email:</label>
                <input type='email' id='email' name='email' required>
            </div>
            
            <div class='form-group'>
                <label for='phone'>Phone:</label>
                <input type='tel' id='phone' name='phone'>
            </div>
            
            <div class='form-group'>
                <label for='address'>Address:</label>
                <textarea id='address' name='address'></textarea>
            </div>
            
            <div class='form-group'>
                <label for='membership_level'>Membership Level:</label>
                <select id='membership_level' name='membership_level'>
                    <option value='Standard'>Standard</option>
                    <option value='Premium'>Premium</option>
                    <option value='Gold'>Gold</option>
                </select>
            </div>
            
            <div class='form-group'>
                <label for='join_date'>Join Date:</label>
                <input type='date' id='join_date' name='join_date' value='" . date('Y-m-d') . "'>
            </div>
            
            <button type='submit' name='create_member'>Create Member</button>
        </form>";
        
        // Example 3: Get member by ID (if ID is provided)
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $memberId = $_GET['id'];
            echo "<h2>Member Details</h2>";
            
            $member = ApiMember::findById($memberId);
            
            if ($member) {
                displayMember($member);
            } else {
                echo "<p>Member not found.</p>";
            }
        }
        
    } else {
        echo "<div class='error'>Authentication failed. Check your API credentials.</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>API Error: " . $e->getMessage() . "</div>";
}

// Add some basic styling
echo "
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
        line-height: 1.6;
    }
    
    h1, h2 {
        color: #333;
        border-bottom: 1px solid #ddd;
        padding-bottom: 5px;
    }
    
    .success {
        color: green;
        padding: 10px;
        background-color: #e8f5e9;
        border: 1px solid #c8e6c9;
        margin-bottom: 15px;
        border-radius: 4px;
    }
    
    .error {
        color: red;
        padding: 10px;
        background-color: #ffebee;
        border: 1px solid #ffcdd2;
        margin-bottom: 15px;
        border-radius: 4px;
    }
    
    .members-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .member {
        border: 1px solid #ddd;
        padding: 15px;
        border-radius: 5px;
        background-color: #f9f9f9;
    }
    
    form {
        max-width: 500px;
        margin: 20px 0;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    
    input, select, textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
    }
    
    textarea {
        height: 80px;
    }
    
    button {
        background-color: #4CAF50;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    
    button:hover {
        background-color: #45a049;
    }
</style>";