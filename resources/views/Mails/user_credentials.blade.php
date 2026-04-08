<p>Hello {{ $credentials['name'] ?? 'User' }},</p>

<p>Your eKalendaryo account has been created. Here are your login credentials:</p>

<p><strong>Name:</strong> {{ $credentials['name'] ?? 'N/A' }}</p>
<p><strong>User ID:</strong> {{ $credentials['userId'] ?? 'N/A' }}</p>
<p><strong>Email:</strong> {{ $credentials['email'] ?? 'N/A' }}</p>
<p><strong>Department:</strong> {{ $credentials['department'] ?? 'N/A' }}</p>
<p><strong>Year Level:</strong> {{ $credentials['yearlevel'] ?? 'N/A' }}</p>
<p><strong>Section:</strong> {{ $credentials['section'] ?? 'N/A' }}</p>
<p><strong>Password:</strong> {{ $credentials['password'] ?? 'N/A' }}</p>

<p>Please log in and change your password immediately.</p>

<p>- eKalendaryo Team</p>
