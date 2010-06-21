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
        
    $frm = new UI_UploadDelete($u);
    
    etag('div', $frm->render());
}

function show_files()
{
    Layout::open('admin')->activate();

    $tab = new UI_UploadsGrid(Upload::open_all());
    etag('div',
        $tab->render(),
        UrlFactory::craft('upload.create')->anchor('Upload a new file')
            ->add_class('button')
            ->add_class('download')
    );
}
?>
