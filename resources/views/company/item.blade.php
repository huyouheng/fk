<div class="row">
    @foreach($items as $item)
        <div class="col-md-6">
            <div class="item">
                <span class="label">{{$item['title']}}:</span>
                <input type="text" class="form-control" required name="{{$item['key']}}" value="{{$item['value']}}"/>
            </div>
        </div>
    @endforeach
</div>
