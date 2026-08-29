<div class="tab-pane" id="site_slots" role="tabpanel">
    <div class="kt-section">
        <h3 class="kt-section__title">
            Настройки TBS-слотов:
        </h3>
        <div class="form-group row">
            <div class="col-lg-4">
                <label>Provider ID:</label>
                <input type="text" class="form-control" placeholder="xxxxxx" value="{{$settings->tbs_provider_id}}" name="tbs_provider_id" />
            </div>
            <div class="col-lg-4">
                <label>Secret:</label>
                <input type="text" class="form-control" placeholder="xxxxxxx" value="{{$settings->tbs_provider_secret}}" name="tbs_provider_secret" />
            </div>
        </div>
    </div>
    <div class="kt-section">
        <h3 class="kt-section__title">
            Настройки B2B-слотов:
        </h3>
        <div class="form-group row">
            <div class="col-lg-4">
                <label>Provider ID:</label>
                <input type="text" class="form-control" placeholder="xxxxxx" value="{{$settings->b2b_provider_id}}" name="b2b_provider_id" />
            </div>
            <div class="col-lg-4">
                <label>Provider ID (YT):</label>
                <input type="text" class="form-control" placeholder="xxxxxxx" value="{{$settings->b2b_provider_yt_id}}" name="b2b_provider_yt_id" />
            </div>
        </div>
    </div>
    <div class="kt-section">
        <h3 class="kt-section__title">
            Выбор провайдера:
        </h3>
        <div class="form-group row">
            <div class="col-lg-4">
                <label for="type">Используемый провайдер:</label>
                <select class="form-control" name="slots_provider">
                    <option value="TBS" @if($settings->slots_provider == 'TBS') selected @endif>TBS</option>
                    <option value="B2B" @if($settings->slots_provider == 'B2B') selected @endif>B2B</option>
                    <option value="COPY" @if($settings->slots_provider == 'COPY') selected @endif>COPY</option>
                </select>  
            </div>
        </div>
    </div>
</div>
