<!DOCTYPE html>
<html>
<head>
    <title>Create Dwolla Customer</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="email"], input[type="password"], input[type="date"] {
            width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px;
        }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; }
        .button { background: #4CAF50; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; }
        .button:hover { background: #45a049; }
        .helper-text { font-size: 12px; color: #666; margin-top: 3px; }
        .alert { padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-danger { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <h1>Create Dwolla Customer</h1>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <form action="{{ url('/create-dwolla-customer') }}" method="POST">
        @csrf
        
        <div class="grid">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
            </div>
            
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
            </div>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
        </div>
        
        <div class="form-group">
            <label for="address_line_1">Address Line 1</label>
            <input type="text" id="address_line_1" name="address_line_1" value="{{ old('address_line_1') }}" required>
        </div>
        
        <div class="form-group">
            <label for="address_line_2">Address Line 2 (Optional)</label>
            <input type="text" id="address_line_2" name="address_line_2" value="{{ old('address_line_2') }}">
        </div>
        
        <div class="grid-3">
            <div class="form-group">
                <label for="city">City</label>
                <input type="text" id="city" name="city" value="{{ old('city') }}" required>
            </div>
            
            <div class="form-group">
                <label for="state">State</label>
                <input type="text" id="state" name="state" value="{{ old('state') }}" maxlength="2" required>
                <div class="helper-text">2-letter code (e.g., CA)</div>
            </div>
            
            <div class="form-group">
                <label for="postal_code">Postal Code</label>
                <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code') }}" required>
            </div>
        </div>
        
        <div class="grid">
            <div class="form-group">
                <label for="date_of_birth">Date of Birth</label>
                <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" required>
                <div class="helper-text">Must be at least 18 years old</div>
            </div>
            
            <div class="form-group">
                <label for="ssn">Social Security Number</label>
                <input type="password" id="ssn" name="ssn" maxlength="9" required>
                <div class="helper-text">9 digits, no dashes</div>
            </div>
        </div>
        
        <div class="form-group">
            <button type="submit" class="button">Create Dwolla Customer</button>
        </div>
    </form>
</body>
</html>