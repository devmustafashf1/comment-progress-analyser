import { Head } from '@inertiajs/react';
import { useState } from "react";

export default function TaskAnalyzer() {
  const [description, setDescription] = useState("");
  const [comments, setComments] = useState([""]);
  const [result, setResult] = useState(null);
  const [loading, setLoading] = useState(false);

  // Handle adding new comment field
  const addComment = () => setComments([...comments, ""]);

  // Handle change in comment field
  const updateComment = (index, value) => {
    const newComments = [...comments];
    newComments[index] = value;
    setComments(newComments);
  };

  // Handle form submit
  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setResult(null);

    try {
      const response = await fetch("/api/analyze-task", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ description, comments }),
      });

      const data = await response.json();
      setResult(data);
    } catch (err) {
      console.error(err);
      setResult({ error: true, message: "Something went wrong" });
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-gray-100 p-6">
      <div className="max-w-3xl mx-auto bg-white shadow rounded-lg p-6">
        <h1 className="text-2xl font-bold mb-4">Task & Comments Analyzer</h1>

        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block font-semibold mb-1">Task Description</label>
            <textarea
              value={description}
              onChange={(e) => setDescription(e.target.value)}
              className="w-full border rounded p-2"
              rows={4}
              required
            />
          </div>

          <div>
            <label className="block font-semibold mb-1">Comments / Updates</label>
            {comments.map((c, i) => (
              <input
                key={i}
                type="text"
                value={c}
                onChange={(e) => updateComment(i, e.target.value)}
                className="w-full border rounded p-2 mb-2"
                placeholder={`Comment #${i + 1}`}
              />
            ))}
            <button
              type="button"
              onClick={addComment}
              className="text-blue-600 hover:underline"
            >
              + Add Comment
            </button>
          </div>

          <button
            type="submit"
            disabled={loading}
            className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50"
          >
            {loading ? "Analyzing..." : "Analyze Task"}
          </button>
        </form>

       {result && (
  <div className="mt-6 p-4 bg-gray-50 border rounded">
    <h2 className="text-xl font-semibold mb-2">Analysis Result</h2>

    {/* Progress Bar */}
    {result.progress && (
      <div className="mb-4">
        <div className="text-sm font-semibold mb-1">Progress: {result.progress}</div>
        <div className="w-full bg-gray-200 rounded h-4">
          <div
            className="bg-blue-600 h-4 rounded"
            style={{ width: result.progress }}
          ></div>
        </div>
      </div>
    )}

    {/* Other analysis */}
    <div className="mt-2">
      <h3 className="font-semibold mb-1">Completed Tasks:</h3>
      <ul className="list-disc list-inside">
        {result.completed_tasks?.map((task, i) => (
          <li key={i}>{task}</li>
        ))}
      </ul>

      <h3 className="font-semibold mt-2 mb-1">Pending Tasks:</h3>
      <ul className="list-disc list-inside">
        {result.pending_tasks?.map((task, i) => (
          <li key={i}>{task}</li>
        ))}
      </ul>

      <h3 className="font-semibold mt-2 mb-1">Open Questions:</h3>
      <ul className="list-disc list-inside">
        {result.open_questions?.map((q, i) => (
          <li key={i}>{q}</li>
        ))}
      </ul>

      <h3 className="font-semibold mt-2 mb-1">Estimated Time to Complete:</h3>
      <p>{result.estimated_time_to_complete}</p>
    </div>
  </div>
)}

      </div>
    </div>
  );
}
