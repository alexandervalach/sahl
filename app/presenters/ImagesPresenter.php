<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\FileSystem;

class ImagesPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $imagesRow;

    /** @var ActiveRow */
    private $albumRow;

    /** @var string */
    private $error = "Image not found";

    public function actionAdd($id) {
        $this->userIsLogged();
        $this->albumRow = $this->albumsRepository->findById($id);
    }

    public function renderAdd($id) {
        $this->template->album = $this->albumRow;
        if (!$this->albumRow) {
            throw new BadRequestException($this->error);
        }
        $this->getComponent('addImagesForm');
    }

    public function actionDelete($id) {
        $this->userIsLogged();
        $this->imagesRow = $this->galleryRepository->findById($id);
    }

    public function renderDelete($id) {
        if (!$this->imagesRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->img = $this->imagesRow;
    }

    public function actionThumbnail($id, $img_id) {
        $this->userIsLogged();
        $this->albumRow = $this->albumsRepository->findById($id);
        $this->imagesRow = $this->galleryRepository->findById($img_id);
        $this->submittedThumbnailForm();
    }

    protected function submittedThumbnailForm() {
        if ($this->imagesRow != NULL) {
            $data['name'] = $this->imagesRow->name;
            $this->albumRow->update($data);
            $this->flashMessage("Nová miniatúra bola nastavená", "success");
            $this->redirect('Albums:all');
        } else {
            $this->flashMessage("Miniatúru sa nepodarilo nastaviť", "danger");
            $this->redirect('Albums:view', $this->albumRow);
        }
    }

}
