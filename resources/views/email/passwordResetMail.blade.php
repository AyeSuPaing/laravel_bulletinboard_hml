<x-mail::message>
# Hello {{ $user->name }}  
Do you want to reset password?

Please click the following link and change password.  
{{ $reset_page_url }}

Thanks,  
From Development Team
</x-mail::message>
