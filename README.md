# Login Protect

![Screenshot of failed login](screenshot.png?raw=true "Login failure screen")

Author: Tom Needham <tom@owncloud.com>

Limit admin login to certain IP ranges.

## Usage

1. Install and enable the app
2. Configure the permitted IP range(s) in CIDR notation using OCC:  
`occ config:app:set loginprotect allowed_ranges --value="192.168.1.1/24"`

Note: You can configure multiple IP ranges by separating them with a comma, and 
even use IPV6 values:  
`occ config:app:set loginprotect allowed_ranges --value="192.168.1.1/24,2001:db8::/48"`