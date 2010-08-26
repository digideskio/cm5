<?php

Stupid::add_rule('upload_file',
    array('type' => 'url_path', 'chunk[3]' => '/^\+upload$/')
);
Stupid::add_rule('edit_file',
    array('type' => 'url_path', 'chunk[3]' => '/^([\d]+)$/', 'chunk[4]' => '/^\+edit$/')
);
Stupid::add_rule('delete_file',
    array('type' => 'url_path', 'chunk[3]' => '/^([\d]+)$/', 'chunk[4]' => '/^\+delete$/')
);
Stupid::set_default_action('show_files');
Stupid::chain_reaction();


function upload_file()
{
    Layout::open('admin')->activate();
    $frm = new UI_UploadFile();
    etag('div',
        $frm->render());
}

function edit_file($id)
{
    Layout::open('admin')->activate();
    if (!($u = Upload::open($id)))
        not_found();
        
    $frm = new UI_UploadEdit($u);
    
    etag('div', $frm->render());
}

function delete_file($id)
{
    Layout::open('admin')->activate();
    if (!($u = Upload::open($id)))
        not_found();
        
    $frm = new UI_ConfirmForm(
        "Delete \"{$u->filename}\"",
        'Are you sure? This action is inreversible!',
        'Delete',
        create_function('$u','
            $u->delete();
            UrlFactory::craft("upload.admin")->redirect();
        '),
        array($u),
        UrlFactory::craft("upload.admin")
    );
    
    etag('div', $frm->render());
}

function show_files()
{
    Layout::open('admin')->activate();

    etag('div',
        UrlFactory::craft('upload.create')->anchor('Upload a new file')
            ->add_class('button')
            ->add_class('download')
    )->add_class('panel uploads');
    $tab = new UI_UploadsGrid(Upload::open_query()->order_by('id', 'DESC')->execute());
    etag('div',  $tab->render());
}
?>
