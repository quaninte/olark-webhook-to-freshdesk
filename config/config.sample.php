<?php

return [
    'debug' => false,
    'freshdesk_subdomain' => 'beeketing',
    'freshdesk_api_key' => 'API_KEY',
    'ticket_subject' => 'Offline message from {{visitor_fullName}}',
    'ticket_description' => '{{conversionMessages}}
----
{{visitor_fullName}} - {{visitor_phoneNumber}}
{{visitor_city}}, {{visitor_region}}, {{visitor_countryCode}}
Conversion begin page: {{visitor_conversationBeginPage}}',
];