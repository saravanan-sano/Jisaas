<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://apis.google.com/js/platform.js" async defer></script>
    <script src="https://accounts.google.com/gsi/client"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://apis.google.com/js/api.js"></script>


    <!-- Add your CSS and other head elements here -->
</head>
<body>
@extends('front.front_layouts')

@section('content')

<section class="relative pt-8 pb-36 bg-gradient-gray2 overflow-hidden">
<div class="g_id_signin"
        data-client_id="803399791668-oh279m2brfb5m0uiv8cce2sqb60gdqr8.apps.googleusercontent.com"
        data-callback="onGoogleSignIn"
        data-context="use"
        data-theme="outline"
        data-text="signin_with"
        data-shape="rectangular">
    </div>
    <img class="absolute top-1/2 transform -translate-y-1/2 left-0" src="{{ $frontSetting->register_background_url }}" alt="">
    <div class="relative z-10 container mx-auto px-4">
        <div class="flex flex-wrap -m-6">
            <div class="w-full md:w-1/2 p-6">
                <div class="md:max-w-xl">
                    <h2 class="mb-3 heading-text">{{ $frontSetting->register_title }}</h2>
                    <p class="mb-12 subheading-text">{{ $frontSetting->register_description }}</p>
                    {!! Form::open(['url' => '', 'class' => ' ajax-form', 'id' => 'ajax-register-form', 'method' => 'POST']) !!}
                    <div>
                        <div id="alert"></div>
                    </div>
                    <div class="register-div">
                        <div class="-m-2 mb-6" style=" display: grid; grid-template-columns: 50% 50%;
                            gap: 10px;">
                                <div class="form-group">
                                    <input
                                    autofocus
                                        class="form-control w-full px-5 py-3.5 text-gray-500 placeholder-gray-500 bg-white outline-none focus:ring-4 focus:ring-indigo-500 border border-gray-200 rounded-lg"
                                        type="text" placeholder="{{ $frontSetting->register_company_name_text }}"
                                        id="company_name" name="company_name">
                                </div>
                                <div class="form-group">
                                    <input
                                        class="form-control w-full px-5 py-3.5 text-gray-500 placeholder-gray-500 bg-white outline-none focus:ring-4 focus:ring-indigo-500 border border-gray-200 rounded-lg"
                                        type="text" placeholder="{{ $frontSetting->register_contact_text }}"
                                        id="contact_name" name="contact_name">
                                </div>
                                <div class="form-group flex w-full">
                                    <select
                                        class="custom-select px-5 py-3.5 text-gray-500 placeholder-gray-500 bg-white outline-none focus:ring-4 focus:ring-indigo-500 border border-gray-200 rounded-lg"
                                        id="countryCode" name="countryCode"
                                        style="border-right: none;
                                        border-bottom-right-radius: 0;
                                        border-top-right-radius: 0;
                                        width:45%;
                                        font-size:12px;88">
                                        <option style="text-align: center" value="">Select Country Code
                                        </option>
                                        @foreach ($countrycode as $country)
                                            <option value="+{{ $country['phonecode'] }}"
                                                @if ($country['iso'] === $country_code) selected @endif>{{ $country['iso'] }}
                                                (+{{ $country['phonecode'] }})
                                            </option>
                                        @endforeach
                                    </select>


                                    <input
                                        class="form-control px-5 py-3.5 text-gray-500 placeholder-gray-500 bg-white outline-none focus:ring-4 focus:ring-indigo-500 border border-gray-200 rounded-lg"
                                        type="phone" placeholder="{{ $frontSetting->register_phone_text }}" id="mobile"
                                        name="mobile" value="{{ $actionEmail }}" maxlength="10" min="10" onkeypress="return isNumber(event)"
                                        style="border-left: none;
                                        border-bottom-left-radius: 0;
                                        border-top-left-radius: 0;
                                        width: 70%;">
                                </div>

                                <div class="form-group">
                                    <input
                                        class="form-control w-full px-5 py-3.5 text-gray-500 placeholder-gray-500 bg-white outline-none focus:ring-4 focus:ring-indigo-500 border border-gray-200 rounded-lg"
                                        type="text" placeholder="{{ $frontSetting->register_email_text }}"
                                        id="company_email" name="company_email" value="{{ $actionEmail }}">
                                </div>
                                <div class="form-group">
                                    <input
                                        class="form-control w-full px-5 py-3.5 text-gray-500 placeholder-gray-500 bg-white outline-none focus:ring-4 focus:ring-indigo-500 border border-gray-200 rounded-lg"
                                        type="password" placeholder="{{ $frontSetting->register_password_text }}"
                                        id="password" name="password">
                                </div>
                                <div class="form-group">
                                    <input
                                        class="form-control w-full px-5 py-3.5 text-gray-500 placeholder-gray-500 bg-white outline-none focus:ring-4 focus:ring-indigo-500 border border-gray-200 rounded-lg"
                                        type="password" placeholder="{{ $frontSetting->register_confirm_password_text }}"
                                        id="confirm_password" name="confirm_password">
                                </div>
                            </div>


                            <div class="flex flex-wrap -m-1.5 mb-8">
                                <div class="flex items-center form-group w-auto p-1.5">
                                    <input class="form-control w-4 h-4" type="checkbox" id="condition" name="condition">
                                    <div class="flex-1 p-1.5">
                                        <p class="text-gray-500 text-sm">
                                            <a class="text-gray-900 hover:text-gray-700"
                                                href="{{ $frontSetting->register_agree_url }}" target="_blank">
                                                {{ $frontSetting->register_agree_text }}
                                            </a>
                                        </p>
                                    </div>
                                </div>

                            </div>

                            @if (config('services.recaptcha.key'))
                                <div class="g-recaptcha  mb-8"  data-sitekey="{{ config('services.recaptcha.key') }}">
                                </div>
                            @endif

                            <div class="group relative md:max-w-max">
                                <div
                                    class="absolute top-0 left-0 w-full h-full bg-purple-600 text-white opacity-0 group-hover:opacity-50 rounded-lg transition ease-out duration-300">
                                </div>
                                <button
                                    class="p-1 w-full font-heading font-semibold text-xs text-gray-900 group-hover:text-white uppercase tracking-px overflow-hidden rounded-md"
                                    type="submit" onclick="register();return false">
                                    <div class="relative p-5 px-9 bg-purple-600 text-white overflow-hidden rounded-md">
                                        <div
                                            class="absolute top-0 left-0 transform -translate-y-full group-hover:-translate-y-0 h-full w-full bg-gray-900 transition ease-in-out duration-500">
                                        </div>
                                        <p class="relative z-10">{{ $frontSetting->register_submit_button_text }}</p>


                                    </div>
                                </button>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
                <div class="w-full md:w-1/2 p-6">
                    <div class="md:max-w-lg ml-auto">
                        <div class="flex flex-wrap -m-6">
                            <div class="flex flex-wrap w-full lg:-m-3 pt-8">
                                <div class="max-w-max mx-auto">
                                    @include('front.includes.header_feature_lists')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')


    <script>
        "use strict";
        function isNumber(evt) {
            evt = (evt) ? evt : window.event;
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                return false;
            }
            return true;
        }
        function register() {

            const credentials ={
            email: document.getElementById("company_email").value,
            password: document.getElementById("password").value,
            mobile: document.getElementById("mobile").value,
            countryCode: document.getElementById("countryCode").value,
                    };
            art.sendXhr({
                url: "{{ route('front.register.save') }}",
                type: "POST",
                file: true,
                container: "#ajax-register-form",
                disableButton: true,
                messageLocation: 'inline',
                success: function(response) {
                    if (response.status == 'success') {

                        sessionStorage.setItem("loginData",  JSON.stringify(credentials));
                        $('.register-div').remove();
                        window.location = "admin/verify";
                    }
                }
            });
        }
    </script>
@endsection
</body>
</html>
