<form id="{{ $arResult["TEMPLATE"] }}" class="feedback_form" action="" method="POST" enctype="multipart/form-data">
	{!! bitrix_sessid_post() !!}
	<input type="hidden" name="PARAMS_HASH" value="{{ $arResult["PARAMS_HASH"] }}">
	<input type="hidden" name="EMPTY" value="" />
	<input type="hidden" name="AJAX" value="Y" />
	<input type="hidden" name="TEMPLATE" value="{{ $arResult["TEMPLATE"] }}" />
	<input type="hidden" name="CURRENT_PAGE_URL" value="" />
	<input type="hidden" name="CURRENT_PAGE" value="" />
	<input type="hidden" name="FORM_TITLE" value="" />

    <h3 class="feedback_form__title"></h3>

    <div class="row"><div class="col-12 feedback_form__errors"></div></div>

	@foreach ($arResult["FIELDS"] as $fieldAlias => $fieldName)
        @continue($fieldAlias == 'ANSWER_TEXT')
        @php
            $require = '';
            if (in_array($fieldAlias, $arParams['REQUIRED_FIELDS'])) $require = 'require';
        @endphp
		<div class="form-group feedback_form__row">
			<span class="form-placeholder feedback_form__placeholder">{{ $fieldName }}</span>
			<input type="text" class="form-control feedback_form__input {{ $require }}" name="{{ $fieldAlias }}" value="" maxlength="30">
		</div>
	@endforeach

    @if ($arResult['FIELDS']['ANSWER_TEXT'])
        @php
            $require = '';
            if (in_array($fieldAlias, $arParams['REQUIRED_FIELDS'])) $require = 'require';
        @endphp
        <div class="form-group feedback_form__row">
            <span class="form-placeholder feedback_form__placeholder">{{ $arResult['FIELDS']['ANSWER_TEXT'] }}</span>
            <textarea class="form-control feedback_form__input {{ $require }}" name="ANSWER_TEXT" style="resize: none;"
                      placeholder="{{ $arResult['FIELDS']['ANSWER_TEXT'] }}"></textarea>
        </div>
    @endif

	@if ($arResult['UPLOAD_FILE'])
		<div class="form-group feedback_form__row">
			<span class="form-placeholder feedback_form__placeholder">Прикрепить файл</span>
			<input type="file" class="form-control-file feedback_form__file-input" name="ANSWER_FILE" value="">
		</div>
	@endif

	<button type="submit" class="btn btn-primary">Отправить</button>
</form>