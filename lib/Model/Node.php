<?php

namespace OCA\FractalNote\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use OCA\FractalNote\Service\Exception\NoChangesException;
use OCA\FractalNote\Service\Exception\NotEditableException;

class Node
{
    private const TYPE_RICH      = 'rich';
    private const TYPE_PLAINTEXT = 'txt';
    private const TYPE_READONLY  = 'readonly';
    private Collection $grids;
    private Collection $codeboxes;
    private Collection $images;
    private Relation $relation;
    private ?Relation $parentRelation;
    private ?Bookmark $bookmark;
    public function __construct(
        private int $nodeId,
        private string $name,
        private string $txt,
        private string $syntax,
        private string $tags,
        private int $isRo,
        private int $isRichTxt,
        private int $hasCodebox,
        private int $hasTable,
        private int $hasImage,
        private int $level,
        private int $tsCreation,
        private int $tsLastSave
    ) {
        $this->images = new ArrayCollection();
        $this->grids = new ArrayCollection();
        $this->codeboxes = new ArrayCollection();
    }

    public function update(?string $title, ?string $content): void
    {
        if (($title === null || $title === $this->name) && ($content === null || $content === $this->txt)) {
            throw new NoChangesException();
        }

        $this->name = $title ?? $this->name;
        if (null !== $content) {
            if (!$this->isEditable()) {
                throw new NotEditableException($this->isRichTxt, $this->isRo);
            }
            $this->txt = $content;
        }
    }

    public function isEditable()
    {
        return !$this->isRo && !$this->isRichTxt;
    }

    public function assignLevelValue(int $level)
    {
        $this->level = $level;
    }

    public function images(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): void
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
        }
    }

    public function removeImage(Image $image): void
    {
        $this->images->removeElement($image);
    }

    public function grids(): Collection
    {
        return $this->grids;
    }

    public function addGrid(Grid $grid): void
    {
        if (!$this->grids->contains($grid)) {
            $this->grids->add($grid);
        }
    }

    public function removeGrid(Grid $grid): void
    {
        $this->grids->removeElement($grid);
    }

    public function codeboxes(): Collection
    {
        return $this->codeboxes;
    }

    public function addCodebox(Codebox $codebox): void
    {
        if (!$this->codeboxes->contains($codebox)) {
            $this->codeboxes->add($codebox);
        }
    }

    public function removeCodebox(Codebox $codebox): void
    {
        $this->codeboxes->removeElement($codebox);
    }

    public function relation(): Relation
    {
        return $this->relation;
    }

    public function parentRelation(): ?Relation
    {
        return $this->parentRelation;
    }

    public function bookmark(): ?Bookmark
    {
        return $this->bookmark;
    }

    public function nodeId(): int
    {
        return $this->nodeId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function content(): string
    {
        return $this->txt;
    }

    public function syntax(): string
    {
        return $this->syntax;
    }

    public function tags(): string
    {
        return $this->tags;
    }

    public function isReadOnly()
    {
        return $this->isRo;
    }

    public function isRich(): int
    {
        return $this->isRichTxt;
    }

    public function hasTable(): int
    {
        return $this->hasTable;
    }

    public function level(): int
    {
        return $this->level;
    }

    public function tsCreation(): ?int
    {
        return $this->tsCreation;
    }

    public function tsLastSave(): ?int
    {
        return $this->tsLastSave;
    }

    public function iconType(): string
    {
        switch (true) {
            case $this->isRich():
                return self::TYPE_RICH;
            case $this->isReadOnly():
                return self::TYPE_READONLY;
            default:
                return self::TYPE_PLAINTEXT;
        }
    }
}
