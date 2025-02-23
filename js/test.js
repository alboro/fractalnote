$(document).ready(function(){
    OC.Plugins.register('OCA.Files.FileActions', {
        attach: function(fileList) {
            alert(1);
            // OCA.Files.fileActions.register('application/cherrytree-ctb', 'OpenCTB', OC.PERMISSION_READ, '', function (filename, context) {
            //     if (filename.endsWith('.ctb')) {
            //         var fileId = context.fileList.getFileId(filename); // Получаем ID файла
            //         var url = OC.generateUrl('/apps/myapp/viewfile/{fileId}', {fileId: fileId});
            // window.document.location.href = OC.generateUrl('/apps/fractalnote') + '?f=' + context.dir + '/' + filename;
            // }
            // });
            // OCA.Files.fileActions.setDefault(mime, 'OpenCTB');
            // OCA.Files.fileActions.setIcon(mime, 'OpenCTB', OC.imagePath('core', 'actions/edit'));
            OCA.Files.fileActions.register(
                'application/cherrytree-ctb',
                'Open CTB File',
                OC.generateUrl('/apps/fractalnote/open_ctb_file'),
                function (filename) {
                    alert('Открываем CTB файл: ' + filename);
                },
                1
            );
            alert(2);
        }
    });

    if (typeof OCA !== 'undefined' && typeof OCA.Files !== 'undefined' && typeof OCA.Files.fileActions !== 'undefined') {
        OCA.Files.fileActions.register(
            'application/cherrytree-ctb', // MIME-тип файла
            'Open CTB File',               // Название действия
            OC.generateUrl('/apps/fractalnote/open_ctb_file'), // URL для открытия вашего приложения
            function(filename, context) {
                var url = OC.generateUrl('/apps/fractalnote') + '?f=' + context.dir + '/' + filename;
                alert(url);
                window.location.href = url;
            },
            1
        );
        // Устанавливаем действие по умолчанию, чтобы оно выполнялось при клике на файл
        OCA.Files.fileActions.setDefault('application/cherrytree-ctb', 'Open CTB File');
    } else {
        // alert('OCA.Files не определен. Этот скрипт должен выполняться на страницах файлового менеджера.');
    }
});
// alert('fin');