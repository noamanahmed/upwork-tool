<?php

return [
  'actions' => 'Actions',
  'search' => 'Search',
  'reset' => 'Reset',
  'submit' => 'Submit',
  'update' => 'Update',
  'loading' => 'Loading',
  'welcome' => 'Welcome',
  'showing' => 'Showing',
  'entries' => 'Entries',
  'items' => 'Items',
  'previous' => 'Previous',
  'next' => 'Next',
  'welcome_message' => 'Welcome to Upwork Management Dashboard',
  'account' =>
  [
    'profile' => 'Profile',
    'settings' => 'Settings',
    'verified' => 'Verified',
    'unverified' => 'Unverified',
    'language' => 'Language',
    'timezone' => 'Timezone',
  ],
  'auth' =>
  [
    'labels' =>
    [
      'first_name' => 'First Name',
      'last_name' => 'Last Name',
      'email' => 'Email',
      'password' => 'Password',
      'password_confirmation' => 'Confirm Password',
      'new_password' => 'New Password',
      'address' => 'Address',
      'city' => 'City',
      'state' => 'State',
      'country' => 'Country',
      'phone' => 'Phone',
      'working_hours' => 'Working Hours',
      'job_type' => 'Job Type',
      'resend_verification_email' => 'Resend Verification Email',
    ],
    'login' =>
    [
      'heading_1' => 'Welcome to Upwork Tool! 👋🏻',
      'heading_2' => 'Please sign-in to your account and start the adventure',
      'remember_me' => 'Remember me',
      'forgot_password' => 'Forgot Password',
      'no_account_message' => 'New on our platform?',
      'register_instead' => 'Create an account',
      'submit' => 'Login',
    ],
    'register' =>
    [
      'heading_1' => 'Adventure starts here 🚀',
      'heading_2' => 'Make your business management easy and fun!',
      'tos_agree' => 'I Agree to Terms of Services(TOS)',
      'already_account' => 'Already have an account?',
      'sign_in_instead' => 'Sign in Instead',
      'submit' => 'Sign Up',
    ],
    'forgot_password' =>
    [
      'heading_1' => 'Forgot Password? 🔒',
      'heading_2' => 'Enter your email and we\'ll send you instructions to reset your password',
      'back_login' => 'Back to Login',
      'submit' => 'Send Reset Link',
    ],
    'reset_password' =>
    [
      'heading_1' => 'Reset your Password 🔒',
      'heading_2' => 'Please make sure to remember your password this time 😏',
      'back_login' => 'Back to Login',
      'submit' => 'Set new Password',
    ],
    'profile' =>
    [
      'monday' => 'Monday',
      'tuesday' => 'Tuesday',
      'wednesday' => 'Wednesday',
      'thursday' => 'Thursday',
      'friday' => 'Friday',
      'starting_hour' => 'Starting Hour',
      'ending_hour' => 'Ending Hour',
    ],
  ],
  'errors' =>
  [
  ],
  'messages' =>
  [
    'modules' =>
    [
        'users' =>
        [
          'created' => 'User has been created successfully',
          'updated' => 'User has been updated successfully',
          'deleted' => 'User has been deleted successfully',
          'not_found' => 'We are unable to find this user',
          'no_data_found' => 'No Users found',
        ],
        'roles' =>
        [
          'created' => 'Role has been created successfully',
          'updated' => 'Role has been updated successfully',
          'deleted' => 'Role has been deleted successfully',
          'not_found' => 'We are unable to find this role',
          'no_data_found' => 'No Roles found',
        ],
    ],
    'account' =>
    [
        'profile' => [
            'success' => 'Your profile has been updated successfully'
        ],
        'settings' => [
            'success' => 'Your settings have been updated successfully'
        ]
    ],
    'auth' =>
    [
        'login' => [
            'success' => 'You have logged in. Please wait while we redirect you to the dashboard.'
        ],
        'logout' => [
            'success' => 'You have been logged out successfully'
        ],
        'forgot_password' =>[
            'success' => 'Please check your email address for an email to reset your password.'
        ],
        'register' => [
            'success' => 'You have registered successfully. Please wait while we redirect you to the dashboard.'
        ],
        'email_verification' => [
            'title' => 'Email Verification Pending',
            'message' => 'Please verify your email by clicking on the link sent to your email address.',
            'success' => 'Please check your email address for an email to verify your account'
        ]
    ]
  ],
  'roles' =>
  [
    'admin' => 'Admin',
    'super_admin' => 'Super Admin',
    'readonly' => 'Read Only',
  ],
  'dashboard' =>
  [
    'menu' =>
    [
      'search' => 'Search',
      'profile' => 'Profile',
      'settings' => 'Settings',
      'help' => 'Help',
      'faq' => 'FAQ',
      'logout' => 'Logout',
      'account' => 'Account',
      'users' => 'Users',
    ],
    'modules' =>
    [
      'users' =>
      [
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'email' => 'Email',
        'status' => 'Status',
        'status_pretty' => 'Status',
        'gender' => 'Gender',
        'phone' => 'Phone',
        'website' => 'Website',
        'company' => 'Company',
        'job_designation' => 'Job Designation',
        'address' => 'Address',
        'city' => 'City',
        'state' => 'State',
        'country' => 'Country',
        'role' => 'Role',
        'type' => 'Type',
        'type_pretty' => 'Type',
        'add' => 'Add New User',
        'update' => 'Update User',
        'delete' => 'Delete User',
        'enums' =>
        [
          'job_type' =>
          [
            'inspector' => 'Inspector',
            'cleaner' => 'Cleaner',
            'repairman' => 'Repairman',
            'adviser' => 'Adviser',
            'reporter' => 'Reporter',
          ],
          'status' =>
          [
            'blocked' => 'Blocked',
            'email_unverified' => 'Email Unverified',
            'active' => 'Active',
            'inactive' => 'Inactive',
          ],
          'type' =>
          [
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'employee' => 'Employee',
            'read_only' => 'Read Only',
          ],
        ],
      ],
      'roles' =>
      [
        'name' => 'Name',
        'status' => 'Status',
        'status_pretty' => 'Status',
        'add' => 'Add New Role',
        'update' => 'Update Role',
        'delete' => 'Delete Role',
        'enums' =>
        [
          'status' =>
          [
            'default' => 'Default',
          ],
        ],
      ],
    ],
  ],
];
