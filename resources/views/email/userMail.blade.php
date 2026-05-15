<x-mail::message>
# Hello {{ $user->name }}  
@if ($update_flg)
Your account has been updated successfully.

**Email:** {{ $user->email }}

Please login again.
@else
Your account has been created successfully.

**Email:** {{ $user->email }}  
**Password:** {{ $passwordText }}

Please login and change your password as soon as possible.
@endif

Thanks,  
From Development Team
</x-mail::message>
