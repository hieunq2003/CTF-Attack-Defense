from django.db import models


class SuriLog(models.Model):
    timestamp = models.DateTimeField(auto_now_add=True)
    content = models.TextField()

    class Meta:
        # Tên của item trong group System log trên admin index
        verbose_name = "System log"
        verbose_name_plural = "System log"

    def __str__(self):
        return f"Log at {self.timestamp}"
