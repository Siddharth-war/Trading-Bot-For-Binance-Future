@if (session()->has('address'))
    @php
        $address = session()->get('address')
    @endphp
    <ul>
        <li>
            <span>{{ translate('Name') }}:</span>
            <strong>{{ $address['contact_person_name'] }}</strong>
        </li>
        <li>
            <span>{{ translate('Contact Number') }}:</span>
            <strong>{{ $address['contact_person_number'] }}</strong>
        </li>
        @if( $address['area_name'] != null)
            <li>
                <span>{{ translate('Area') }}:</span>
                <strong>{{ $address['area_name'] }}</strong>
            </li>
        @endif

    </ul>
    <div class="location">
        <i class="tio-poi"></i>
        <span>
            {{ $address['address'] }}
        </span>
    </div>
@endif
