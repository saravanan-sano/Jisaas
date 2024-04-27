@extends('front.front_layouts1')

@section('content')
<section class="relative pt-32 pb-36 bg-gradient-gray2 overflow-hidden">
    <img class="absolute top-1/2 transform -translate-y-1/2 left-0" src="{{ $frontSetting->register_background_url }}" alt="">
    <div class="relative z-10 container mx-auto px-4">
        <div class="flex flex-wrap -m-6">
            <div class="w-full md:w-1/2 p-6">
                <div class="md:max-w-xl">
                    <h2 class="mb-3 heading-text">{{ $frontSetting->register_title_thanks }}</h2>
                    <p class="mb-12 subheading-text">{{ $frontSetting->register_description_thanks }}</p>
                    <div class="flex flex-wrap justify-center items-center -m-1">
          
          <div class="w-auto p-1">
          <div class="w-full lg:w-auto p-3 text-center">
              <a href="{{ route('main', ['path' => 'admin/login']) }}"  class="group relative font-heading font-semibold w-full md:w-auto py-5 px-8 text-xs text-white bg-gray-900 hover:bg-gray-800 uppercase overflow-hidden rounded-md tracking-px">
                  {{ $frontSetting->login_button_text }}
              </a>
          </div>
          </div>
      </div>
                    </div>
                </div>
                <div class="w-full md:w-1/2 p-6">
                    <div class="md:max-w-lg ml-auto">
                        <div class="flex flex-wrap -m-6">
                            <div class="flex flex-wrap w-full lg:-m-3">
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

        function register() {
            art.sendXhr({
                url: "{{ route('front.register.save') }}",
                type: "POST",
                file: true,
                container: "#ajax-register-form",
                disableButton: true,
                messageLocation: 'inline',
                success: function(response) {
                    if (response.status == 'success') {
                        $('.register-div').remove();
                    }
                }
            });
        }
    </script>
@endsection
